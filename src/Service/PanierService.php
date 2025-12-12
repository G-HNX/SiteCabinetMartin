<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PanierService implements EventSubscriberInterface
{
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
        $items = $this->all();
        $count = 0;
        foreach ($items as $qty) {
            $count += (int)$qty;
        }
        $session->set('count', $count);
    }

    private const KEY = 'cart_items';

    public function __construct(private RequestStack $requestStack) {}

    private function save(array $items): void
    {
        $this->requestStack->getSession()->set(self::KEY, $items);
    }

    public function all(): array
    {
        return $this->requestStack->getSession()->get(self::KEY, []);
    }

    public function add(int $id, int $qty = 1): void
    {
        $items = $this->all();
        $items[$id] = ($items[$id] ?? 0) + max(1, $qty);
        $this->save($items);
    }

    public function decrease(int $id, int $step = 1): void
    {
        $items = $this->all();
        if (isset($items[$id])) {
            $items[$id] -= $step;
            if ($items[$id] <= 0) {
                unset($items[$id]);
            }
            $this->save($items);
        }
    }

    public function remove(int $id): void
    {
        $items = $this->all();
        unset($items[$id]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function detailed(array $catalog): array
    {
        $rows = [];
        $total = 0.0;
        $count = 0;

        foreach ($this->all() as $id => $qty) {
            $p = null;
            foreach ($catalog as $item) {
                if ($item['id'] == $id) {
                    $p = $item;
                    break;
                }
            }
            if (!$p) continue;

            $lineTotal = (float)$p['prix'] * (int)$qty;
            $rows[] = [
                'id' => (int)$id,
                'nom' => $p['nom'],
                'prix' => (float)$p['prix'],
                'image' => $p['image'] ?? null,
                'qty' => (int)$qty,
                'total' => $lineTotal,
            ];
            $total += $lineTotal;
            $count += (int)$qty;
        }

        return ['items' => $rows, 'total' => $total, 'count' => $count];
    }
}