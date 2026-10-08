<?php

namespace App\Controller;

use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    private const CSRF_ID = 'panier';

    #[Route('/panier', name: 'app_panier', methods: ['GET'])]
    public function index(PanierService $cart): Response
    {
        $items = $cart->detailed();

        return $this->render('panier/index.html.twig', [
            'items' => $items,
            'total' => $cart->total($items),
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_panier_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(int $id, Request $request, PanierService $cart): Response
    {
        if ($this->checkToken($request)) {
            $cart->add($id, $request->request->getInt('quantite', 1));
            $this->addFlash('success', 'Produit ajouté au panier.');
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/diminuer/{id}', name: 'app_panier_decrease', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function decrease(int $id, Request $request, PanierService $cart): Response
    {
        if ($this->checkToken($request)) {
            $cart->decrease($id);
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/retirer/{id}', name: 'app_panier_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(int $id, Request $request, PanierService $cart): Response
    {
        if ($this->checkToken($request)) {
            $cart->remove($id);
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'app_panier_clear', methods: ['POST'])]
    public function clear(Request $request, PanierService $cart): Response
    {
        if ($this->checkToken($request)) {
            $cart->clear();
        }

        return $this->redirectToRoute('app_panier');
    }

    private function checkToken(Request $request): bool
    {
        if ($this->isCsrfTokenValid(self::CSRF_ID, $request->request->getString('_token'))) {
            return true;
        }

        $this->addFlash('error', 'Session expirée, veuillez réessayer.');

        return false;
    }
}
