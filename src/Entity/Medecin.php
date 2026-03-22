<?php

namespace App\Entity;

use App\Repository\MedecinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $numSiretMedecin = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $specMedecin = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'medecin')]
    private Collection $lesRendezVous;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $personneMedecin = null;

    public function __construct()
    {
        $this->lesRendezVous = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumSiretMedecin(): ?string
    {
        return $this->numSiretMedecin;
    }

    public function setNumSiretMedecin(?string $numSiretMedecin): static
    {
        $this->numSiretMedecin = $numSiretMedecin;

        return $this;
    }

    public function getSpecMedecin(): ?string
    {
        return $this->specMedecin;
    }

    public function setSpecMedecin(?string $specMedecin): static
    {
        $this->specMedecin = $specMedecin;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getLesRendezVous(): Collection
    {
        return $this->lesRendezVous;
    }

    public function addLesRendezVou(RendezVous $lesRendezVou): static
    {
        if (!$this->lesRendezVous->contains($lesRendezVou)) {
            $this->lesRendezVous->add($lesRendezVou);
            $lesRendezVou->setMedecin($this);
        }

        return $this;
    }

    public function removeLesRendezVou(RendezVous $lesRendezVou): static
    {
        if ($this->lesRendezVous->removeElement($lesRendezVou)) {
            // set the owning side to null (unless already changed)
            if ($lesRendezVou->getMedecin() === $this) {
                $lesRendezVou->setMedecin(null);
            }
        }

        return $this;
    }

    public function getPersonneMedecin(): ?Personne
    {
        return $this->personneMedecin;
    }

    public function setPersonneMedecin(Personne $personneMedecin): static
    {
        $this->personneMedecin = $personneMedecin;

        return $this;
    }
}
