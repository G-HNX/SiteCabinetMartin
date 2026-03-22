<?php

namespace App\Service;

use App\Entity\Medicament;
use App\Entity\PanierItem;
use App\Entity\User;
use App\Repository\PanierItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class PanierService implements EventSubscriberInterface
{
    private const KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private PanierItemRepository $panierItemRepository,
        private TokenStorageInterface $tokenStorage,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    // -------------------------------------------------
    // Utilisateur courant (null si non connecte)
    // -------------------------------------------------
    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }
        $user = $token->getUser();
        return $user instanceof User ? $user : null;
    }

    // -------------------------------------------------
    // Mise a jour du compteur de session a chaque requete
    // -------------------------------------------------
    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $count = 0;
        foreach ($this->getPanier() as $qty) {
            $count += (int) $qty;
        }

        $event->getRequest()->getSession()->set('count', $count);
    }

    // -------------------------------------------------
    // Fusion session -> BD a la connexion
    // -------------------------------------------------
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $session = $this->requestStack->getSession();
        $sessionItems = $session->get(self::KEY, []);

        foreach ($sessionItems as $id => $qty) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);
            if (!$medicament) {
                continue;
            }
            $item = $this->panierItemRepository->findOneBy(['user' => $user, 'medicament' => $medicament]);
            if ($item) {
                $item->setQuantite($item->getQuantite() + $qty);
            } else {
                $item = new PanierItem();
                $item->setUser($user);
                $item->setMedicament($medicament);
                $item->setQuantite($qty);
                $this->entityManager->persist($item);
            }
        }

        $this->entityManager->flush();
        $session->remove(self::KEY);
    }

    // -------------------------------------------------
    // Recuperer le panier sous forme [id => quantite]
    // -------------------------------------------------
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

    // -------------------------------------------------
    // Ajouter un produit
    // -------------------------------------------------
    public function add(int $id, int $qty = 1): void
    {
        $user = $this->getUser();
        if ($user) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);
            if (!$medicament) {
                return;
            }
            $item = $this->panierItemRepository->findOneBy(['user' => $user, 'medicament' => $medicament]);
            if ($item) {
                $item->setQuantite($item->getQuantite() + max(1, $qty));
            } else {
                $item = new PanierItem();
                $item->setUser($user);
                $item->setMedicament($medicament);
                $item->setQuantite(max(1, $qty));
                $this->entityManager->persist($item);
            }
            $this->entityManager->flush();
            return;
        }

        $items = $this->requestStack->getSession()->get(self::KEY, []);
        $items[$id] = ($items[$id] ?? 0) + max(1, $qty);
        $this->requestStack->getSession()->set(self::KEY, $items);
    }

    // -------------------------------------------------
    // Diminuer la quantite
    // -------------------------------------------------
    public function decrease(int $id, int $step = 1): void
    {
        $user = $this->getUser();
        if ($user) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);
            if (!$medicament) {
                return;
            }
            $item = $this->panierItemRepository->findOneBy(['user' => $user, 'medicament' => $medicament]);
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

    // -------------------------------------------------
    // Supprimer un produit
    // -------------------------------------------------
    public function remove(int $id): void
    {
        $user = $this->getUser();
        if ($user) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);
            if (!$medicament) {
                return;
            }
            $item = $this->panierItemRepository->findOneBy(['user' => $user, 'medicament' => $medicament]);
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

    // -------------------------------------------------
    // Vider le panier
    // -------------------------------------------------
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

        $this->requestStack->getSession()->set(self::KEY, []);
    }

    // -------------------------------------------------
    // Details du panier (pour les templates)
    // -------------------------------------------------
    public function detailed(): array
    {
        $result = [];
        foreach ($this->getPanier() as $id => $quantite) {
            $medicament = $this->entityManager->getRepository(Medicament::class)->find($id);
            if ($medicament) {
                $result[] = [
                    'id'       => $id,
                    'nom'      => $medicament->getNom(),
                    'prix'     => $medicament->getPrix(),
                    'quantite' => $quantite,
                    'image'    => $medicament->getImage(),
                    'stock'    => $medicament->getStock(),
                ];
            }
        }
        return $result;
    }
}
