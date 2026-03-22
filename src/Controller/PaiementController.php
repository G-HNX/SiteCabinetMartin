<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Medicament;
use App\Entity\User;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class PaiementController extends AbstractController
{
    #[Route('/paiement', name: 'app_paiement', methods: ['GET'])]
    public function index(PanierService $cart): Response
    {
        $items = $cart->detailed();
        $total = array_sum(array_map(fn($i) => $i['prix'] * $i['quantite'], $items));

        return $this->render('paiement/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/paiement/payer', name: 'app_paiement_payer', methods: ['POST'])]
    public function payer(EntityManagerInterface $entityManager, PanierService $cart, MailerInterface $mailer): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $personne = $user->getPersonne();
        $patient = $personne->getPatient();

        // 2. Récupérer les articles du panier
        $items = $cart->getPanier();
        if (empty($items)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        // 3. Début de transaction
        $entityManager->beginTransaction();

        try {
            // 4. Création de la commande
            $commande = new Commande();
            $commande->setDateCommande(new \DateTime());
            $commande->setPersonne($personne);

            $entityManager->persist($commande);

            // 5. Pour chaque article → créer une ligne + décrémenter stock
            foreach ($items as $medicamentId => $quantite) {
                $medicament = $entityManager->getRepository(Medicament::class)->find($medicamentId);

                if (!$medicament || $medicament->getStock() < $quantite) {
                    throw new \Exception("Stock insuffisant pour " . ($medicament?->getNom() ?? "#$medicamentId"));
                }

                // Ligne commande
                $ligne = new LigneCommande();
                $ligne->setCommande($commande);
                $ligne->setMedicamentLigneCommande($medicament);
                $ligne->setQuantite($quantite);
                $ligne->setPrix($medicament->getPrix());

                $entityManager->persist($ligne);

                // Décrémentation du stock
                $medicament->setStock($medicament->getStock() - $quantite);
            }

            // 6. Validation
            $entityManager->flush();
            $entityManager->commit();

            // 7. Vider le panier
            $cart->clear();

            // 8. Email de confirmation
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('cabinetmartinonline535@gmail.com', 'Cabinet Martin'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre commande #' . $commande->getId())
                    ->htmlTemplate('email/commande_confirmation.html.twig')
                    ->context(['commande' => $commande]);
                $mailer->send($email);
            } catch (\Exception) {
                // L'envoi d'email ne bloque pas la commande
            }

            $this->addFlash('success', 'Paiement effectué avec succès !');
            return $this->redirectToRoute('app_profil');

        } catch (\Exception $e) {
            $entityManager->rollback();
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_panier');
        }
    }
}