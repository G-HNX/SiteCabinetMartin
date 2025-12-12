<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

final class GlobalCartSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SessionInterface $session,
        private Environment $twig
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) return;

        // Lecture directe du panier en session
        $cart = $this->session->get('cart', []);

        // Injection dans Twig comme variable globale
        $this->twig->addGlobal('global_cart', $cart);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}