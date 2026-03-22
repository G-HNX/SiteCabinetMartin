<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Repository\MedecinRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class RendezVousController extends AbstractController
{
  #[Route('/rendezvous/{mode}/{day}', name: 'app_rendez_vous', defaults: ['mode' => 'move', 'day' => 0])]
  public function index(Request $request, MedecinRepository $medecinRepository, string $mode = 'move', int $day = 0): Response
  {
    // --- Offset persistant en session (0 = aujourd'hui) ---
    $session = $request->getSession();
    $offset = (int) $session->get('rdv_offset', 0);

    // Modes :
    // - move: décale l'offset courant de {day} (peut être ±1, ±7, …), clamp à [0..+inf]
    // - set: fixe l'offset à {day} (clamp à [0..+inf])
    // - reset: remet à 0 (aujourd'hui)
    switch ($mode) {
      case 'move':
        $offset += (int) $day;
        break;
      case 'set':
        $offset = (int) $day;
        break;
      case 'reset':
        $offset = 0;
        break;
    }

    if ($offset < 0) {
      $offset = 0; // on ne va jamais après aujourd'hui
    }

    $session->set('rdv_offset', $offset);

    // Medecins depuis la base de donnees
    $doctors = [];
    foreach ($medecinRepository->findAll() as $m) {
      $doctors[] = [
        'id' => $m->getId(),
        'name' => 'Dr #' . $m->getId(),
        'speciality' => $m->getSpecMedecin() ?? 'Medecine generale',
      ];
    }

    $tz = new DateTimeZone('Europe/Paris');
    $today = new DateTimeImmutable('today', $tz); // minuit à Paris
    $anchor = $today->modify('+' . $offset . ' day');

    $businessDays = 7;
    $slotMins = 30;
    $ranges = [['08:00', '12:00'], ['13:00', '18:00']];

    $booked = [];

    // Génération lundi→vendredi à partir de l'ancre
    $calendar = [];
    $cursor = $anchor;

    while (count($calendar) < $businessDays) {
      if ((int) $cursor->format('N') <= 5) {
        $ymd = $cursor->format('Y-m-d');
        $calendar[$ymd] = ['label' => $cursor->format('D d/m'), 'slots' => []];
      }

      $cursor = $cursor->modify('+1 day');
    }

    foreach ($doctors as $doc) {
      foreach ($calendar as $ymd => &$col) {
        $col['slots'][$doc['id']] = $this->buildSlotsForDay($ymd, $ranges, $slotMins, $doc['id'], $booked);
      }
      unset($col);
    }

    return $this->render('rendez_vous/index.html.twig', [
      'doctors' => $doctors,
      'calendar' => $calendar,
      'offset' => $offset, // pour l'UI
    ]);
  }

  private function buildSlotsForDay(string $ymd, array $ranges, int $slotMins, string $doctorId, array $booked): array
  {
    $out = [];

    foreach ($ranges as [$hStart, $hEnd]) {
      $tz = new DateTimeZone('Europe/Paris');
      $from = new DateTimeImmutable("$ymd $hStart", $tz);
      $to = new DateTimeImmutable("$ymd $hEnd", $tz);

      $period = new DatePeriod($from, new DateInterval("PT{$slotMins}M"), $to);

      foreach ($period as $dt) {
        $iso = $dt->format('Y-m-d\\TH:i');
        $key = $doctorId . '|' . $dt->format('Y-m-d H:i'); // pour booked

        $out[] = [
          'time' => $dt->format('H:i'),
          'iso' => $iso,
          'available' => !in_array($key, $booked, true),
        ];
      }
    }

    return $out;
  }

  #[Route('/reserver/{doctor}/{slot}', name: 'reservation_new', requirements: ['slot' => '[0-9T:-]+'])]
  public function reservation(string $doctor, string $slot): Response
  {
    // On ne touche pas à l'offset (il reste tel qu'il est en session)
    return $this->render('rendez_vous/reservation.html.twig', [
      'doctor' => $doctor,
      'slot' => $slot,
    ]);
  }

  #[Route('/validation/{doctor}/{slot}/{motif}', name: 'reservation_add', requirements: ['slot' => '[0-9T:-]+'])]
  public function validate(string $doctor, string $slot, string $motif, EntityManagerInterface $em, MailerInterface $mailer): Response
  {
    $this->denyAccessUnlessGranted('ROLE_USER');

    $dt = DateTimeImmutable::createFromFormat('Y-m-d\\TH:i', $slot);
    if (!$dt) {
      throw $this->createNotFoundException('Créneau invalide');
    }

    $medecin = $em->getRepository(Medecin::class)->find((int) $doctor);
    if (!$medecin) {
      throw $this->createNotFoundException('Médecin introuvable');
    }

    $patient = $this->getUser()->getPersonne()->getPatient();

    $rdv = new RendezVous();
    $rdv->setDateDebutRDV($dt);
    $rdv->setDateFinRDV($dt->modify('+30 minutes'));
    $rdv->setCommentaireRDV($motif);
    $rdv->setDisponibiliteRDV(false);
    $rdv->setMedecin($medecin);
    $rdv->setPatient($patient);

    $em->persist($rdv);
    $em->flush();

    // Email de confirmation
    try {
      $email = (new TemplatedEmail())
        ->from(new Address('cabinetmartinonline535@gmail.com', 'Cabinet Martin'))
        ->to($this->getUser()->getEmail())
        ->subject('Confirmation de votre rendez-vous du ' . $dt->format('d/m/Y à H:i'))
        ->htmlTemplate('email/rdv_confirmation.html.twig')
        ->context(['rdv' => $rdv]);
      $mailer->send($email);
    } catch (\Exception) {
      // L'envoi d'email ne bloque pas la confirmation
    }

    $this->addFlash('success', sprintf('Rendez-vous confirmé le %s.', $dt->format('d/m/Y H:i')));

    return $this->redirectToRoute('app_profil');
  }
}
