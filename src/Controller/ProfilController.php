<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\RendezVousRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil', methods: ['GET'])]
    public function profil(RendezVousRepository $rdvRepository, CommandeRepository $commandeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $personne = $user->getPersonne();
        $patient = $personne?->getPatient();

        $rdvs = $patient ? $rdvRepository->findForPatient($patient) : [];

        return $this->render('profil/index.html.twig', [
            'personne' => $personne,
            'patient' => $patient,
            'rdvsAVenir' => array_reverse(array_values(array_filter($rdvs, fn ($r) => $r->isAVenir()))),
            'rdvsPasses' => array_values(array_filter($rdvs, fn ($r) => !$r->isAVenir())),
            'commandes' => $personne ? $commandeRepository->findBy(['personne' => $personne], ['dateCommande' => 'DESC']) : [],
        ]);
    }
}
