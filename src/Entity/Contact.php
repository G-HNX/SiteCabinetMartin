<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nomContact = null;

    #[ORM\Column(length: 255)]
    private ?string $emailContact = null;

    #[ORM\Column]
    private ?\DateTime $dateContact = null;

    #[ORM\Column(length: 255)]
    private ?string $motifContact = null;

    #[ORM\ManyToOne]
    private ?Personne $personne = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomContact(): ?string
    {
        return $this->nomContact;
    }

    public function setNomContact(string $nomContact): static
    {
        $this->nomContact = $nomContact;

        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->emailContact;
    }

    public function setEmailContact(string $emailContact): static
    {
        $this->emailContact = $emailContact;

        return $this;
    }

    public function getDateContact(): ?\DateTime
    {
        return $this->dateContact;
    }

    public function setDateContact(\DateTime $dateContact): static
    {
        $this->dateContact = $dateContact;

        return $this;
    }

    public function getMotifContact(): ?string
    {
        return $this->motifContact;
    }

    public function setMotifContact(string $motifContact): static
    {
        $this->motifContact = $motifContact;

        return $this;
    }

    public function getPersonne(): ?Personne
    {
        return $this->personne;
    }

    public function setPersonne(?Personne $personne): static
    {
        $this->personne = $personne;

        return $this;
    }
}
