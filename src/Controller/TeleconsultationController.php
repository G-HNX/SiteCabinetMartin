<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeleconsultationController extends AbstractController
{
    #[Route('/teleconsultation', name: 'app_teleconsultation')]
    public function index(): Response
    {
        return $this->render('teleconsultation/index.html.twig', [
            'controller_name' => 'TeleconsultationController',
        ]);
    }
}
