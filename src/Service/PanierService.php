<?php

namespace App\Service;

use App\Entity\Medicament;
use App\Entity\PanierItem;
use App\Entity\User;
use App\Repository\MedicamentRepository;
use App\Repository\PanierItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Panier hybride : en session pour un visiteur anonyme, en base (PanierItem)
 * pour un utilisateur connecté. Le panier de session est fusionné à la connexion.
 */
final class PanierService implements EventSubscriberInterface
{
    private const KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private PanierItemRepository $panierItemRepository,
        private MedicamentRepository $medicamentRepository,
        private TokenStorageInterface $tokenStorage,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    private function getUser(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof User ? $user : null;
    }

    private function findItem(User $user, int $medicamentId): ?PanierItem
    {
        return $this->panierItemRepository->findOneBy(['user' => $user, 'medicament' => $medicamentId]);
    }

    // -------------------------------------------------
    // Fusion session -> BD a la connexion
    // -------------------------------------------------
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $session = $event->getRequest()->getSession();
        $sessionItems = $session->get(self::KEY, []);

        if (!$user instanceof User || !$sessionItems) {
            return;
        }

        foreach ($sessionItems as $id => $qty) {
            $medicament = $this->medicamentRepository->find($id);
            if (!$medicament) {
                continue;
            }
            $item = $this->findItem($user, $id);
            if (!$item) {
                $item = (new PanierItem())->setUser($user)->setMedicament($medicament);
                $this->entityManager->persist($item);
            }
            $item->setQuantite(min($medicament->getStock(), $item->getQuantite() + $qty));
        }

        $this->entityManager->flush();
        $session->remove(self::KEY);
    }

    /**
     * @return array<int, int> [medicamentId => quantite]
     */
    public function getPanier(): array
    {
        $user = $this->getUser();
        if ($user) {
            $items = [];
            foreach ($this->panierItemRepository->findBy(['user' => $user]) as $item) {
                $items[$item->getMedicament()->getId()] = $item->getQuantite();
            }

            return $items;
        }

        return $this->requestStack->getSession()->get(self::KEY, []);
    }

    /**
     * Ajoute un produit, sans dépasser le stock disponible.
     */
    public function add(int $id, int $qty = 1): void
    {
        $medicament = $this->medicamentRepository->find($id);
        if (!$medicament || $medicament->getStock() <= 0) {
            return;
        }
        $qty = max(1, $qty);

        $user = $this->getUser();
        if ($user) {
            $item = $this->findItem($user, $id);
            if (!$item) {
                $item = (new PanierItem())->setUser($user)->setMedicament($medicament);
                $this->entityManager->persist($item);
            }
            $item->setQuantite(min($medicament->getStock(), $item->getQuantite() + $qty));
            $this->entityManager->flush();

            return;
        }

        $items = $this->requestStack->getSession()->get(self::KEY, []);
        $items[$id] = min($medicament->getStock(), ($items[$id] ?? 0) + $qty);
        $this->requestStack->getSession()->set(self::KEY, $items);
    }

    public function decrease(int $id, int $step = 1): void
    {
        $user = $this->getUser();
        if ($user) {
            $item = $this->findItem($user, $id);
            if ($item) {
                $newQty = $item->getQuantite() - $step;
                if ($newQty <= 0) {
                    $this->entityManager->remove($item);
                } else {
                    $item->setQuantite($newQty);
                }
                $this->entityManager->flush();
            }

            return;
        }

        $items = $this->requestStack->getSession()->get(self::KEY, []);
        if (isset($items[$id])) {
            $items[$id] -= $step;
            if ($items[$id] <= 0) {
                unset($items[$id]);
            }
            $this->requestStack->getSession()->set(self::KEY, $items);
        }
    }

    public function remove(int $id): void
    {
        $user = $this->getUser();
        if ($user) {
            $item = $this->findItem($user, $id);
            if ($item) {
                $this->entityManager->remove($item);
                $this->entityManager->flush();
            }

            return;
        }

        $items = $this->requestStack->getSession()->get(self::KEY, []);
        unset($items[$id]);
        $this->requestStack->getSession()->set(self::KEY, $items);
    }

    public function clear(): void
    {
        $user = $this->getUser();
        if ($user) {
            foreach ($this->panierItemRepository->findBy(['user' => $user]) as $item) {
                $this->entityManager->remove($item);
            }
            $this->entityManager->flush();

            return;
        }

        $this->requestStack->getSession()->remove(self::KEY);
    }

    /**
     * Nombre total d'articles (badge de la navbar). Ne démarre pas de session
     * pour un visiteur qui n'en a pas encore.
     */
    public function count(): int
    {
        if (!$this->getUser()) {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request?->hasPreviousSession()) {
                return 0;
            }
        }

        return array_sum($this->getPanier());
    }

    /**
     * Détail du panier pour les templates (une seule requête).
     *
     * @return list<array{id: int, nom: string, prix: string, quantite: int, image: string, stock: int, sousTotal: string}>
     */
    public function detailed(): array
    {
        $panier = $this->getPanier();
        if (!$panier) {
            return [];
        }

        $result = [];
        /** @var Medicament $medicament */
        foreach ($this->medicamentRepository->findBy(['id' => array_keys($panier)]) as $medicament) {
            $quantite = $panier[$medicament->getId()];
            $result[] = [
                'id' => $medicament->getId(),
                'nom' => $medicament->getNom(),
                'prix' => $medicament->getPrix(),
                'quantite' => $quantite,
                'image' => $medicament->getImage(),
                'stock' => $medicament->getStock(),
                'sousTotal' => number_format((float) $medicament->getPrix() * $quantite, 2, '.', ''),
            ];
        }

        return $result;
    }

    /**
     * @param list<array{sousTotal: string}>|null $items
     */
    public function total(?array $items = null): string
    {
        $total = 0.0;
        foreach ($items ?? $this->detailed() as $item) {
            $total += (float) $item['sousTotal'];
        }

        return number_format($total, 2, '.', '');
    }
}
