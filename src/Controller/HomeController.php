<?php

namespace App\Controller;

use App\Repository\MedecinRepository;
use App\Repository\MedicamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(MedecinRepository $medecinRepository, MedicamentRepository $medicamentRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'medecins' => $medecinRepository->findAllWithPersonne(),
            'produits' => $medicamentRepository->findBy([], ['nom' => 'ASC'], 4),
        ]);
    }
}
