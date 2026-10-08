<?php

namespace App\Twig;

use App\Service\PanierService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PanierExtension extends AbstractExtension
{
    public function __construct(private PanierService $panier) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_count', $this->panier->count(...)),
        ];
    }
}
