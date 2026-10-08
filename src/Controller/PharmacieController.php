<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Medicament;
use App\Repository\CategorieRepository;
use App\Repository\MedicamentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PharmacieController extends AbstractController
{
    #[Route('/pharmacie', name: 'app_pharmacie', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('pharmacie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/pharmacie/liste/{cat}', name: 'app_pharmacie_cat', requirements: ['cat' => '\d+'], methods: ['GET'])]
    public function liste(#[MapEntity(id: 'cat')] Categorie $categorie, CategorieRepository $categorieRepository, MedicamentRepository $medicamentRepository): Response
    {
        return $this->render('pharmacie/produitcat.html.twig', [
            'categorie' => $categorie,
            'products' => $medicamentRepository->findByCategorie($categorie),
            'categories' => $categorieRepository->findAll(),
            'cat' => $categorie->getId(),
        ]);
    }

    #[Route('/pharmacie/{cat}/{id}', name: 'app_pharmacie_medicament', requirements: ['cat' => '\d+', 'id' => '\d+'], methods: ['GET'])]
    public function detail(int $cat, #[MapEntity(id: 'id')] Medicament $product, CategorieRepository $categorieRepository): Response
    {
        // L'URL doit correspondre à la catégorie réelle du produit
        if ($product->getCategorie()?->getId() !== $cat) {
            return $this->redirectToRoute('app_pharmacie_medicament', [
                'cat' => $product->getCategorie()?->getId(),
                'id' => $product->getId(),
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('pharmacie/detail.html.twig', [
            'categorie' => $product->getCategorie(),
            'product' => $product,
            'categories' => $categorieRepository->findAll(),
            'cat' => $cat,
        ]);
    }
}
