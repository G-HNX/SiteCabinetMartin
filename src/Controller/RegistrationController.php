<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Personne;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private LoggerInterface $logger,
    ) {}

    #[Route('/inscription', name: 'app_inscription')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profil');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            // Chaque inscrit est un patient : User -> Personne -> Patient
            $personne = (new Personne())
                ->setNom($form->get('nom')->getData())
                ->setPrenom($form->get('prenom')->getData())
                ->setTelephone($form->get('telephone')->getData());
            $personne->setPersonneUser($user);

            $patient = new Patient();
            $patient->setPersonne($personne);

            $entityManager->persist($user);
            $entityManager->persist($personne);
            $entityManager->persist($patient);
            $entityManager->flush();

            try {
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->to((string) $user->getEmail())
                        ->subject('Confirmez votre adresse email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
            } catch (\Throwable $e) {
                // Le compte est créé même si l'email ne part pas
                $this->logger->warning('Email de vérification non envoyé', ['exception' => $e]);
            }

            $this->addFlash('success', 'Bienvenue ! Un email de confirmation vient de vous être envoyé.');

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $redirect = $this->getUser() ? 'app_profil' : 'app_login';
        $user = $userRepository->find((int) $request->query->get('id'));

        if (!$user) {
            return $this->redirectToRoute($redirect);
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute($redirect);
        }

        $this->addFlash('success', 'Votre adresse email a bien été vérifiée.');

        return $this->redirectToRoute($redirect);
    }

    #[Route('/verify/renvoyer', name: 'app_verify_resend', methods: ['POST'])]
    public function resend(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isVerified() || !$this->isCsrfTokenValid('verify_resend', $request->request->getString('_token'))) {
            return $this->redirectToRoute('app_profil');
        }

        try {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->to((string) $user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            $this->addFlash('success', 'Un nouvel email de confirmation vous a été envoyé.');
        } catch (\Throwable $e) {
            $this->logger->warning('Email de vérification non envoyé', ['exception' => $e]);
            $this->addFlash('error', 'L\'email n\'a pas pu être envoyé, réessayez plus tard.');
        }

        return $this->redirectToRoute('app_profil');
    }
}
