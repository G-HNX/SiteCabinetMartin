<?php
namespace App\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class TwigPanierSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private Environment $twig;

    public function __construct(RequestStack $requestStack, Environment $twig)
    {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $session = $this->requestStack->getSession();
        if ($session) {
            // Lecture directe du panier en session
            $panier = $session->get('cart_items', []);

            // Injection dans Twig comme variable globale
            $this->twig->addGlobal('global_panier', $panier);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}