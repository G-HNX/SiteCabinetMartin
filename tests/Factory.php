<?php

namespace App\Tests;

use App\Entity\Categorie;
use App\Entity\Medecin;
use App\Entity\Medicament;
use App\Entity\Patient;
use App\Entity\Personne;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Création de données minimales pour les tests (chaque test est isolé
 * dans une transaction annulée par DAMADoctrineTestBundle).
 */
final class Factory
{
    public function __construct(private EntityManagerInterface $em) {}

    public function medicament(int $stock = 10, string $prix = '4.50'): Medicament
    {
        $categorie = (new Categorie())->setNom('Antalgiques')->setDescription('Douleur et fièvre');
        $medicament = (new Medicament())
            ->setNom('Paracétamol')
            ->setForme('Comprimé')
            ->setDosage(1000)
            ->setPrix($prix)
            ->setStock($stock)
            ->setImage('img/meds/paracetamol.jpg')
            ->setDescription('Antalgique.')
            ->setCategorie($categorie);

        $this->em->persist($categorie);
        $this->em->persist($medicament);
        $this->em->flush();

        return $medicament;
    }

    public function medecin(): Medecin
    {
        $medecin = (new Medecin())
            ->setPersonneMedecin((new Personne())->setPrenom('Claire')->setNom('Dubois'))
            ->setSpecMedecin('Pédiatrie');

        $this->em->persist($medecin);
        $this->em->flush();

        return $medecin;
    }

    public function patient(string $email = 'patient@test.fr'): User
    {
        $user = (new User())->setEmail($email)->setPassword('x')->setIsVerified(true);
        $personne = (new Personne())->setPrenom('Sophie')->setNom('Lambert');
        $personne->setPersonneUser($user);
        $patient = new Patient();
        $patient->setPersonne($personne);

        $this->em->persist($user);
        $this->em->persist($personne);
        $this->em->persist($patient);
        $this->em->flush();

        return $user;
    }
}
