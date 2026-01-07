<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Medicament;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PanierController extends AbstractController
{
    /** @var array<int, array<string, mixed>> */
    private array $categories = [];

    /** @var array<int, array<string, mixed>> */
    private array $medicaments = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        // Charger tous les médicaments depuis la base
        $medicaments = $entityManager->getRepository(Medicament::class)->findAll();
        foreach ($medicaments as $medicament) {
            $this->medicaments[] = $this->transformObjetTableau($medicament);
        }

        // Charger toutes les catégories depuis la base
        $categories = $entityManager->getRepository(Categorie::class)->findAll();
        foreach ($categories as $categorie) {
            $this->categories[] = $this->transformObjetTableau($categorie);
        }
    }

    /**
     * Transforme un objet Doctrine en tableau compatible avec le PanierService
     */
    public function transformObjetTableau($objet): ?array
    {
        if (!$objet) {
            return null;
        }

        if ($objet instanceof Medicament) {
            return [
                'id' => $objet->getId(),
                'nom' => $objet->getNom(),
                'forme' => $objet->getForme(),
                'dosage' => $objet->getDosage(),
                'prix' => $objet->getPrix(),
                'description' => $objet->getDescription(),
                'image' => $objet->getImage(),
                'stock' => $objet->getStock(),
                'cat_id' => $objet->getCategorie()->getId(),
            ];
        }

        if ($objet instanceof Categorie) {
            return [
                'id' => $objet->getId(),
                'nom' => $objet->getNom(),
                'description' => $objet->getDescription(),
            ];
        }

        return null;
    }

    /**
     * Trouver un médicament dans le tableau transformé
     */
    public function findOne(int $id): ?array
    {
        foreach ($this->medicaments as $m) {
            if ($m['id'] === $id) {
                return $m;
            }
        }
        return null;
    }

    #[Route('/panier', name: 'app_panier')]
    public function index(PanierService $cart): Response
    {
        $items = $cart->detailed();  
        $total = 0;

        foreach ($items as $item) {
            $total += $item['prix'] * $item['quantite'];
        }

        return $this->render('panier/index.html.twig', [
            'items' => $items,   
            'total' => $total
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_panier_add', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function add(int $id, PanierService $cart): Response
    {
        $cart->add($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/diminuer/{id}', name: 'app_panier_decrease', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function decrease(int $id, PanierService $cart): Response
    {
        $cart->decrease($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/retirer/{id}', name: 'app_panier_remove', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function remove(int $id, PanierService $cart): Response
    {
        $cart->remove($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'app_panier_clear', methods: ['POST','GET'])]
    public function clear(PanierService $cart): Response
    {
        $cart->clear();
        return $this->redirectToRoute('app_panier');
    }
}