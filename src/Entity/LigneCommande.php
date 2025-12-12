<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $qtiteLigneCommande = null;

    #[ORM\ManyToOne(inversedBy: 'lignesCommande')]
    private ?Commande $commande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQtiteLigneCommande(): ?int
    {
        return $this->qtiteLigneCommande;
    }

    public function setQtiteLigneCommande(int $qtiteLigneCommande): static
    {
        $this->qtiteLigneCommande = $qtiteLigneCommande;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }
}
