<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Medicament;
use App\Entity\User;
use App\Service\PanierService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PaiementController extends AbstractController
{
    #[Route('/paiement', name: 'app_paiement', methods: ['GET'])]
    public function index(PanierService $cart): Response
    {
        $items = $cart->detailed();
        if (!$items) {
            return $this->redirectToRoute('app_panier');
        }

        return $this->render('paiement/index.html.twig', [
            'items' => $items,
            'total' => $cart->total($items),
        ]);
    }

    /**
     * Paiement simulé (démo) : crée la commande, décrémente le stock et vide le panier.
     */
    #[Route('/paiement/payer', name: 'app_paiement_payer', methods: ['POST'])]
    public function payer(
        Request $request,
        EntityManagerInterface $entityManager,
        PanierService $cart,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Response {
        if (!$this->isCsrfTokenValid('paiement', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Session expirée, veuillez réessayer.');

            return $this->redirectToRoute('app_paiement');
        }

        /** @var User $user */
        $user = $this->getUser();
        $personne = $user->getPersonne();
        if (!$personne) {
            $this->addFlash('error', 'Votre profil est incomplet, impossible de passer commande.');

            return $this->redirectToRoute('app_profil');
        }

        $items = $cart->getPanier();
        if (!$items) {
            $this->addFlash('error', 'Votre panier est vide.');

            return $this->redirectToRoute('app_panier');
        }

        $entityManager->beginTransaction();

        try {
            $commande = (new Commande())
                ->setDateCommande(new \DateTime())
                ->setPersonne($personne);

            // Verrous toujours pris dans le même ordre pour éviter les deadlocks entre paiements concurrents
            ksort($items);

            $total = 0.0;
            foreach ($items as $medicamentId => $quantite) {
                // Verrou pessimiste : évite de vendre deux fois le dernier article
                $medicament = $entityManager->find(Medicament::class, $medicamentId, LockMode::PESSIMISTIC_WRITE);

                if (!$medicament || $medicament->getStock() < $quantite) {
                    throw new \DomainException(sprintf('Stock insuffisant pour %s.', $medicament?->getNom() ?? 'un produit'));
                }

                $ligne = (new LigneCommande())
                    ->setMedicamentLigneCommande($medicament)
                    ->setQuantite($quantite)
                    ->setPrix($medicament->getPrix());
                $commande->addLignesCommande($ligne);

                $medicament->setStock($medicament->getStock() - $quantite);
                $total += (float) $ligne->getSousTotal();
            }

            $commande->setTotal(number_format($total, 2, '.', ''));
            $entityManager->persist($commande);
            $entityManager->flush();
            $entityManager->commit();
        } catch (\Throwable $e) {
            $entityManager->rollback();

            if ($e instanceof \DomainException) {
                $this->addFlash('error', $e->getMessage());
            } else {
                $logger->error('Échec du paiement', ['exception' => $e]);
                $this->addFlash('error', 'Le paiement a échoué, veuillez réessayer.');
            }

            return $this->redirectToRoute('app_panier');
        }

        $cart->clear();

        try {
            $mailer->send((new TemplatedEmail())
                ->to((string) $user->getEmail())
                ->subject('Confirmation de votre commande n°'.$commande->getId())
                ->htmlTemplate('email/commande_confirmation.html.twig')
                ->context(['commande' => $commande]));
        } catch (\Throwable $e) {
            // L'envoi d'email ne bloque pas la commande
            $logger->warning('Email de confirmation de commande non envoyé', ['exception' => $e]);
        }

        $this->addFlash('success', 'Paiement effectué avec succès ! Votre commande n°'.$commande->getId().' est confirmée.');

        return $this->redirectToRoute('app_profil');
    }
}
