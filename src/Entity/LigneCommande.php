<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\ManyToOne(inversedBy: 'lignesCommande')]
    private ?Medicament $medicamentLigneCommande = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

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

    public function getMedicamentLigneCommande(): ?Medicament
    {
        return $this->medicamentLigneCommande;
    }

    public function setMedicamentLigneCommande(?Medicament $medicamentLigneCommande): static
    {
        $this->medicamentLigneCommande = $medicamentLigneCommande;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        $this->qtiteLigneCommande = $quantite;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}
