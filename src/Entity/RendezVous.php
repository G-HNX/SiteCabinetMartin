<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $dateDebutRDV ;

    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $dateFinRDV;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaireRDV = null;

    #[ORM\Column]
    private ?bool $disponibiliteRDV = null;

   

    #[ORM\ManyToOne(inversedBy: 'lesRendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private Medecin $medecin;

    #[ORM\ManyToOne(inversedBy: 'lesRendezVous')]
    private ?Patient $patient = null;

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

    public function isDisponibiliteRDV(): ?bool
    {
        return $this->disponibiliteRDV;
    }

    public function setDisponibiliteRDV(bool $disponibiliteRDV): static
    {
        $this->disponibiliteRDV = $disponibiliteRDV;

        return $this;
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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }
}
