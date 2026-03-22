<?php

namespace App\Entity;

use App\Repository\PanierItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierItemRepository::class)]
#[ORM\UniqueConstraint(columns: ['user_id', 'medicament_id'])]
class PanierItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medicament $medicament = null;

    #[ORM\Column]
    private int $quantite = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getMedicament(): ?Medicament
    {
        return $this->medicament;
    }

    public function setMedicament(Medicament $medicament): static
    {
        $this->medicament = $medicament;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }
}
