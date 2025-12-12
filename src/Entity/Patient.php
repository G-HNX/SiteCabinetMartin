<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'Patient')]
    private Collection $lesRendezVous;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $patientPersonne = null;

    public function __construct()
    {
        $this->lesRendezVous = new ArrayCollection();       
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $lesRendezVou->setPatient($this);
        }

        return $this;
    }

    public function removeLesRendezVou(RendezVous $lesRendezVou): static
    {
        if ($this->LesRendezVous->removeElement($lesRendezVou)) {
            // set the owning side to null (unless already changed)
            if ($lesRendezVou->getPatient() === $this) {
                $lesRendezVou->setPatient(null);
            }
        }

        return $this;
    }

    public function getPatientPersonne(): ?Personne
    {
        return $this->patientPersonne;
    }

    public function setPatientPersonne(Personne $patientPersonne): static
    {
        $this->patientPersonne = $patientPersonne;

        return $this;
    }

}
