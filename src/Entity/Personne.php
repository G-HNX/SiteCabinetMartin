<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
class Personne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   

    #[ORM\OneToOne(mappedBy: 'personne', cascade: ['persist', 'remove'])]
    private ?Patient $patient = null;

    #[ORM\OneToOne(mappedBy: 'personne', cascade: ['persist', 'remove'])]
    private ?User $PersonneUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    
    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        if ($patient && $patient->getPersonne() !== $this) {
            $patient->setPersonne($this);
        }

        return $this;
    }

    public function getPersonneUser(): ?User
    {
        return $this->PersonneUser;
    }

    public function setPersonneUser(?User $PersonneUser): static
    {
        // unset the owning side of the relation if necessary
        if ($PersonneUser === null && $this->PersonneUser !== null) {
            $this->PersonneUser->setPersonne(null);
        }

        // set the owning side of the relation if necessary
        if ($PersonneUser !== null && $PersonneUser->getPersonne() !== $this) {
            $PersonneUser->setPersonne($this);
        }

        $this->PersonneUser = $PersonneUser;

        return $this;
    }
}