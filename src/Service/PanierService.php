<?php

namespace App\Service;

use App\Entity\Medicament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PanierService implements EventSubscriberInterface
{
    private const KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $items = $this->getPanier();
        $count = 0;

        foreach ($items as $qty) {
            $count += (int)$qty;
        }

        $session->set('count', $count);
    }

    // -----------------------------
    // Récupérer le panier
    // -----------------------------
    public function getPanier(): array
    {
        return $this->requestStack->getSession()->get(self::KEY, []);
    }

    private function save(array $items): void
    {
        $this->requestStack->getSession()->set(self::KEY, $items);
    }

    // -----------------------------
    // Ajouter un produit
    // -----------------------------
    public function add(int $id, int $qty = 1): void
    {
        $items = $this->getPanier();
        $items[$id] = ($items[$id] ?? 0) + max(1, $qty);
        $this->save($items);
    }

    // -----------------------------
    // Diminuer la quantité
    // -----------------------------
    public function decrease(int $id, int $step = 1): void
    {
        $items = $this->getPanier();

        if (isset($items[$id])) {
            $items[$id] -= $step;

            if ($items[$id] <= 0) {
                unset($items[$id]);
            }

            $this->save($items);
        }
    }

    // -----------------------------
    // Supprimer un produit
    // -----------------------------
    public function remove(int $id): void
    {
        $items = $this->getPanier();
        unset($items[$id]);
        $this->save($items);
    }

    // -----------------------------
    // Vider le panier
    // -----------------------------
    public function clear(): void
    {
        $this->save([]);
    }

    // -----------------------------
    // Détails du panier (Doctrine)
    // -----------------------------
    public function detailed(): array
    {
        $panier = $this->getPanier();
        $result = [];

        foreach ($panier as $id => $quantite) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);

            if ($medicament) {
                $result[] = [
                    'id' => $id,
                    'nom' => $medicament->getNom(),
                    'prix' => $medicament->getPrix(),
                    'quantite' => $quantite,
                    'image' => $medicament->getImage(),
                    'stock' => $medicament->getStock(),
                ];
            }
        }

        return $result;
    }
}