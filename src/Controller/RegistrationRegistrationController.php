<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationRegistrationController extends AbstractController
{
    #[Route('/registration/registration', name: 'app_registration_registration')]
    public function index(): Response
    {
        return $this->render('registration_registration/index.html.twig', [
            'controller_name' => 'RegistrationRegistrationController',
        ]);
    }
}
