<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_rdv_medecin_debut', fields: ['medecin', 'dateDebutRDV'])]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $dateDebutRDV;

    #[ORM\Column]
    private \DateTimeImmutable $dateFinRDV;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaireRDV = null;

    #[ORM\ManyToOne(inversedBy: 'lesRendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private Medecin $medecin;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Patient $patient;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebutRDV(): \DateTimeImmutable
    {
        return $this->dateDebutRDV;
    }

    public function setDateDebutRDV(\DateTimeImmutable $dateDebutRDV): static
    {
        $this->dateDebutRDV = $dateDebutRDV;

        return $this;
    }

    public function getDateFinRDV(): \DateTimeImmutable
    {
        return $this->dateFinRDV;
    }

    public function setDateFinRDV(\DateTimeImmutable $dateFinRDV): static
    {
        $this->dateFinRDV = $dateFinRDV;

        return $this;
    }

    public function getCommentaireRDV(): ?string
    {
        return $this->commentaireRDV;
    }

    public function setCommentaireRDV(?string $commentaireRDV): static
    {
        $this->commentaireRDV = $commentaireRDV;

        return $this;
    }

    public function isAVenir(): bool
    {
        return $this->dateDebutRDV > new \DateTimeImmutable();
    }

    public function getMedecin(): Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getPatient(): Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }
}
