<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Medicament;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaiementController extends AbstractController
{
    #[Route('/paiement', name: 'app_paiement', methods: ['GET'])]
    public function index(PanierService $cart): Response
    {
        // Affiche le panier avant paiement
        $data = $cart->detailed();
        return $this->render('paiement/index.html.twig', $data);
    }

    #[Route('/paiement/payer', name: 'app_paiement_payer', methods: ['POST'])]
    public function payer(EntityManagerInterface $entityManager, PanierService $cart): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
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
            $commande->setCommandePersonne($personne);

            $entityManager->persist($commande);

            // 5. Pour chaque article → créer une ligne + décrémenter stock
            foreach ($items as $item) {
                $medicament = $entityManager->getRepository(Medicament::class)->find($item['id']);

                if (!$medicament || $medicament->getStock() < $item['quantite']) {
                    throw new \Exception("Stock insuffisant pour " . $item['nom']);
                }

                // Ligne commande
                $ligne = new LigneCommande();
                $ligne->setCommande($commande);
                $ligne->setMedicament($medicament);
                $ligne->setQuantite($item['quantite']);
                $ligne->setPrix($medicament->getPrix());

                $entityManager->persist($ligne);

                // Décrémentation du stock
                $medicament->setStock($medicament->getStock() - $item['quantite']);
            }

            // 6. Validation
            $entityManager->flush();
            $entityManager->commit();

            // 7. Vider le panier
            $cart->clear();

            $this->addFlash('success', 'Paiement effectué avec succès !');
            return $this->redirectToRoute('app_profil');

        } catch (\Exception $e) {
            $entityManager->rollback();
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_panier');
        }
    }
}