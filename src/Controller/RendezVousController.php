<?php

namespace App\Controller;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RendezVousController extends AbstractController
{
  #[Route('/rendezvous/{mode}/{day}', name: 'app_rendez_vous', defaults: ['mode' => 'move', 'day' => 0])]
  public function index(Request $request, string $mode = 'move', int $day = 0): Response
  {
    // --- Offset persistant en session (0 = aujourd'hui) ---
    $session = $request->getSession();
    $offset = (int) $session->get('rdv_offset', 0);

    // Modes :
    // - move: décale l’offset courant de {day} (peut être ±1, ±7, …), clamp à [0..+inf]
    // - set: fixe l’offset à {day} (clamp à [0..+inf])
    // - reset: remet à 0 (aujourd’hui)
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
      $offset = 0; // on ne va jamais après aujourd’hui
    }

    $session->set('rdv_offset', $offset);

    // Données d’exemple
    $doctors = [
      ['id' => 'martin', 'name' => 'Dr Martin', 'speciality' => 'Médecine interne'],
      ['id' => 'house', 'name' => 'Dr House', 'speciality' => 'Médecine générale'],
    ];

    $tz = new DateTimeZone('Europe/Paris');
    $today = new DateTimeImmutable('today', $tz); // minuit à Paris
    $anchor = $today->modify('+' . $offset . ' day');

    $businessDays = 7;
    $slotMins = 30;
    $ranges = [['08:00', '12:00'], ['13:00', '18:00']];

    // Réservations ou indisponibilités d’exemple (non décalées)
    $booked = [
      'martin|' . $today->modify('+1 day')->format('Y-m-d') . ' 09:00',
      'martin|' . $today->modify('+1 day')->format('Y-m-d') . ' 10:30',
      'house|' . $today->modify('+2 day')->format('Y-m-d') . ' 14:00',
    ];

    // Génération lundi→vendredi à partir de l’ancre
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
      'offset' => $offset, // pour l’UI
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
    // On ne touche pas à l’offset (il reste tel qu’il est en session)
    return $this->render('rendez_vous/reservation.html.twig', [
      'doctor' => $doctor,
      'slot' => $slot,
    ]);
  }

  #[Route('/validation/{doctor}/{slot}/{motif}', name: 'reservation_add', requirements: ['slot' => '[0-9T:-]+'])]
  public function validate(string $doctor, string $slot, string $motif): Response
  {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d\\TH:i', $slot);

    if (! $dt) {
      throw $this->createNotFoundException('Créneau invalide');
    }

    $this->addFlash('success', sprintf('Créneau réservé avec %s le %s.', $doctor, $dt->format('d/m/Y H:i')));

  }
}
