<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Entity\User;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RendezVousController extends AbstractController
{
    private const TIMEZONE = 'Europe/Paris';
    private const BUSINESS_DAYS = 5;
    private const SLOT_MINUTES = 30;
    private const RANGES = [['08:00', '12:00'], ['13:00', '18:00']];

    /**
     * Grille des créneaux sur 5 jours ouvrés.
     * - move : décale la semaine affichée de {day} jours
     * - set  : fixe le décalage à {day}
     * - reset: revient à aujourd'hui
     */
    #[Route('/rendezvous/{mode}/{day}', name: 'app_rendez_vous', requirements: ['mode' => 'move|set|reset', 'day' => '-?\d+'], defaults: ['mode' => 'move', 'day' => 0], methods: ['GET'])]
    public function index(Request $request, MedecinRepository $medecinRepository, RendezVousRepository $rdvRepository, string $mode = 'move', int $day = 0): Response
    {
        $session = $request->getSession();
        $offset = match ($mode) {
            'set' => $day,
            'reset' => 0,
            default => (int) $session->get('rdv_offset', 0) + $day,
        };
        $offset = max(0, $offset); // jamais avant aujourd'hui
        $session->set('rdv_offset', $offset);

        $tz = new DateTimeZone(self::TIMEZONE);
        $now = new DateTimeImmutable('now', $tz);
        $anchor = (new DateTimeImmutable('today', $tz))->modify('+'.$offset.' day');

        // Jours ouvrés à partir de l'ancre
        $days = [];
        $cursor = $anchor;
        while (count($days) < self::BUSINESS_DAYS) {
            if ((int) $cursor->format('N') <= 5) {
                $days[] = $cursor;
            }
            $cursor = $cursor->modify('+1 day');
        }

        $booked = array_flip($rdvRepository->findBookedKeys($anchor, $cursor));
        $medecins = $medecinRepository->findAllWithPersonne();

        $calendar = [];
        foreach ($days as $date) {
            $ymd = $date->format('Y-m-d');
            $calendar[$ymd] = ['date' => $date, 'slots' => []];
            foreach ($medecins as $medecin) {
                $calendar[$ymd]['slots'][$medecin->getId()] = $this->buildSlotsForDay($date, $medecin->getId(), $booked, $now);
            }
        }

        return $this->render('rendez_vous/index.html.twig', [
            'medecins' => $medecins,
            'calendar' => $calendar,
            'offset' => $offset,
        ]);
    }

    #[Route('/reserver/{doctor}/{slot}', name: 'reservation_new', requirements: ['doctor' => '\d+', 'slot' => '\d{4}-\d{2}-\d{2}T\d{2}:\d{2}'], methods: ['GET'])]
    public function reservation(#[MapEntity(id: 'doctor')] Medecin $doctor, string $slot): Response
    {
        $dt = $this->parseSlot($slot);
        if (!$dt) {
            $this->addFlash('error', 'Ce créneau n\'est pas disponible.');

            return $this->redirectToRoute('app_rendez_vous');
        }

        return $this->render('rendez_vous/reservation.html.twig', [
            'doctor' => $doctor,
            'slot' => $slot,
            'date' => $dt,
        ]);
    }

    #[Route('/validation/{doctor}/{slot}', name: 'reservation_add', requirements: ['doctor' => '\d+', 'slot' => '\d{4}-\d{2}-\d{2}T\d{2}:\d{2}'], methods: ['POST'])]
    public function validate(
        #[MapEntity(id: 'doctor')] Medecin $doctor,
        string $slot,
        Request $request,
        EntityManagerInterface $em,
        RendezVousRepository $rdvRepository,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Response {
        if (!$this->isCsrfTokenValid('rdv', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Session expirée, veuillez réessayer.');

            return $this->redirectToRoute('reservation_new', ['doctor' => $doctor->getId(), 'slot' => $slot]);
        }

        $dt = $this->parseSlot($slot);
        if (!$dt) {
            $this->addFlash('error', 'Ce créneau n\'est pas disponible.');

            return $this->redirectToRoute('app_rendez_vous');
        }

        /** @var User $user */
        $user = $this->getUser();
        $patient = $user->getPersonne()?->getPatient();
        if (!$patient) {
            $this->addFlash('error', 'Seul un compte patient peut prendre rendez-vous.');

            return $this->redirectToRoute('app_profil');
        }

        if ($rdvRepository->findOneBy(['medecin' => $doctor, 'dateDebutRDV' => $dt])) {
            $this->addFlash('error', 'Ce créneau vient d\'être réservé, veuillez en choisir un autre.');

            return $this->redirectToRoute('app_rendez_vous');
        }

        $motif = trim($request->request->getString('motif'));
        $rdv = (new RendezVous())
            ->setDateDebutRDV($dt)
            ->setDateFinRDV($dt->modify('+'.self::SLOT_MINUTES.' minutes'))
            ->setCommentaireRDV(mb_substr($motif !== '' ? $motif : 'Consultation', 0, 255))
            ->setMedecin($doctor)
            ->setPatient($patient);

        try {
            $em->persist($rdv);
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            // Réservation concurrente sur le même créneau
            $this->addFlash('error', 'Ce créneau vient d\'être réservé, veuillez en choisir un autre.');

            return $this->redirectToRoute('app_rendez_vous');
        }

        try {
            $mailer->send((new TemplatedEmail())
                ->to((string) $user->getEmail())
                ->subject('Confirmation de votre rendez-vous du '.$dt->format('d/m/Y à H:i'))
                ->htmlTemplate('email/rdv_confirmation.html.twig')
                ->context(['rdv' => $rdv]));
        } catch (\Throwable $e) {
            // L'envoi d'email ne bloque pas la confirmation
            $logger->warning('Email de confirmation de RDV non envoyé', ['exception' => $e]);
        }

        $this->addFlash('success', sprintf('Rendez-vous confirmé le %s avec %s.', $dt->format('d/m/Y à H:i'), $doctor->getNomComplet()));

        return $this->redirectToRoute('app_profil');
    }

    /**
     * @param array<string, int> $booked clés "{medecinId}|Y-m-d H:i"
     *
     * @return list<array{time: string, iso: string, available: bool}>
     */
    private function buildSlotsForDay(DateTimeImmutable $date, int $medecinId, array $booked, DateTimeImmutable $now): array
    {
        $out = [];
        $ymd = $date->format('Y-m-d');

        foreach (self::RANGES as [$hStart, $hEnd]) {
            $period = new DatePeriod(
                new DateTimeImmutable("$ymd $hStart", $date->getTimezone()),
                new DateInterval('PT'.self::SLOT_MINUTES.'M'),
                new DateTimeImmutable("$ymd $hEnd", $date->getTimezone())
            );

            foreach ($period as $dt) {
                $out[] = [
                    'time' => $dt->format('H:i'),
                    'iso' => $dt->format('Y-m-d\TH:i'),
                    'available' => $dt > $now && !isset($booked[$medecinId.'|'.$dt->format('Y-m-d H:i')]),
                ];
            }
        }

        return $out;
    }

    /**
     * Retourne le créneau s'il est valide : futur, jour ouvré, aligné sur la grille et dans les horaires.
     */
    private function parseSlot(string $slot): ?DateTimeImmutable
    {
        $tz = new DateTimeZone(self::TIMEZONE);
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $slot, $tz);

        if (!$dt || $dt->format('Y-m-d\TH:i') !== $slot) {
            return null;
        }
        if ($dt <= new DateTimeImmutable('now', $tz) || (int) $dt->format('N') > 5 || (int) $dt->format('i') % self::SLOT_MINUTES !== 0) {
            return null;
        }

        $time = $dt->format('H:i');
        foreach (self::RANGES as [$start, $end]) {
            if ($time >= $start && $time < $end) {
                return $dt;
            }
        }

        return null;
    }
}
