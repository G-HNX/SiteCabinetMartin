<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setDateContact(new \DateTime());

            $user = $this->getUser();
            if ($user) {
                $contact->setPersonne($user->getPersonne());
            }

            $em->persist($contact);
            $em->flush();

            $this->addFlash('success', 'Votre message a bien ete envoye.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form,
        ]);
    }
}
