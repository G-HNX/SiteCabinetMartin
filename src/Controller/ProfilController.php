<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\RendezVous;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function profil(EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la personne et le patient liés à l'utilisateur
        $personne = $user->getPersonne();
        
        $patient = $personne->getPatient();

        // Récupérer les rendez-vous du patient
        $rdvs = $entityManager->getRepository(RendezVous::class)
            ->findBy(['patient' => $patient]);

        // Récupérer les commandes de la personne
        $commandes = $entityManager->getRepository(Commande::class)
            ->findBy(['personne' => $personne]);
           

        return $this->render('profil/index.html.twig', [
            'personne' => $personne,
            'patient' => $patient,
            'rdvs' => $rdvs,
            'commandes' => $commandes
        ]);
    }
}