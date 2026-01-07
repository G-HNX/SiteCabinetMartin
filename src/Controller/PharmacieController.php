<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Medicament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PharmacieController extends AbstractController
{
    #[Route('/pharmacie', name: 'app_pharmacie')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager
            ->getRepository(Categorie::class)
            ->findAll();

        return $this->render('pharmacie/index.html.twig', [
            'controller_name' => 'PharmacieController',
            'categories' => $categories
        ]);
    }

    #[Route('/pharmacie/liste/{cat}', name: 'app_pharmacie_cat', requirements: ['cat' => '\d+'])]
    public function liste(EntityManagerInterface $entityManager, int $cat): Response
    {
        $categorie = $entityManager->getRepository(Categorie::class)->findOneCategorie($cat);
        $products = $entityManager->getRepository(Medicament::class)->findByCategorie($cat);

        // Si ton template a besoin de toutes les catégories :
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('pharmacie/produitcat.html.twig', [
            'controller_name' => 'PharmacieController',
            'categorie' => $categorie,
            'products' => $products,
            'categories' => $categories,
            'cat' => $cat 
        ]);
    }

    #[Route('/pharmacie/{cat}/{id}', name: 'app_pharmacie_medicament', requirements: ['cat' => '\d+', 'id' => '\d+'])]
    public function detail(EntityManagerInterface $entityManager, int $cat, int $id): Response
    {
        $categorie = $entityManager->getRepository(Categorie::class)->findOneCategorie($cat);
        $product = $entityManager->getRepository(Medicament::class)->findOneMedicament($id);
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('pharmacie/detail.html.twig', [
            'controller_name' => 'PharmacieController',
            'categorie' => $categorie,
            'product' => $product,
            'categories' => $categories,
            'cat' => $cat
        ]);
    }
}