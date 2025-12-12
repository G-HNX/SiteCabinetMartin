<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
class Personne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

     public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $idPers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomPers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomPers = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rolePers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailPers = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telPers = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPers(): ?int
    {
        return $this->idPers;
    }

    public function setIdPers(int $idPers): static
    {
        $this->idPers = $idPers;

        return $this;
    }

    public function getNomPers(): ?string
    {
        return $this->nomPers;
    }

    public function setNomPers(?string $nomPers): static
    {
        $this->nomPers = $nomPers;

        return $this;
    }

    public function getPrenomPers(): ?string
    {
        return $this->prenomPers;
    }

    public function setPrenomPers(?string $prenomPers): static
    {
        $this->prenomPers = $prenomPers;

        return $this;
    }

    public function getRolePers(): ?string
    {
        return $this->rolePers;
    }

    public function setRolePers(?string $rolePers): static
    {
        $this->rolePers = $rolePers;

        return $this;
    }

    public function getEmailPers(): ?string
    {
        return $this->emailPers;
    }

    public function setEmailPers(?string $emailPers): static
    {
        $this->emailPers = $emailPers;

        return $this;
    }

    public function getTelPers(): ?string
    {
        return $this->telPers;
    }

    public function setTelPers(?string $telPers): static
    {
        $this->telPers = $telPers;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    
    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setPersonne($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getPersonne() === $this) {
                $commande->setPersonne(null);
            }
        }

        return $this;
    }
}
