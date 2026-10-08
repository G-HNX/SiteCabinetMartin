<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Medecin;
use App\Entity\Medicament;
use App\Entity\Patient;
use App\Entity\Personne;
use App\Entity\RendezVous;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de données de démonstration.
 *
 *   php bin/console doctrine:fixtures:load -n
 *
 * Comptes : demo@cabinet-martin.fr / demo1234 (patient)
 *           dr.martin@cabinet-martin.fr / demo1234 (médecin)
 */
class AppFixtures extends Fixture
{
    public const DEMO_PASSWORD = 'demo1234';

    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $medicaments = $this->loadPharmacie($manager);
        $medecins = $this->loadMedecins($manager);

        $demo = $this->createPatient($manager, 'demo@cabinet-martin.fr', 'Sophie', 'Lambert', '06 12 34 56 78');
        $autre = $this->createPatient($manager, 'lucas.bernard@example.com', 'Lucas', 'Bernard', '06 98 76 54 32');

        // --- Commandes passées du compte démo ---
        $this->createCommande($manager, $demo->getPersonne(), new \DateTime('-20 days 11:42'), [
            [$medicaments['Paracétamol'], 2],
            [$medicaments['Ibuprofène'], 1],
        ]);
        $this->createCommande($manager, $demo->getPersonne(), new \DateTime('-3 days 18:05'), [
            [$medicaments['Amoxicilline'], 1],
        ]);

        // --- Rendez-vous (toujours relatifs à la semaine courante pour rester valides) ---
        $lundi = new \DateTimeImmutable('monday this week');
        $lundiProchain = $lundi->modify('+7 days');

        $this->createRdv($manager, $demo, $medecins[0], $lundi->modify('-14 days')->setTime(10, 0), 'Bilan annuel');
        $this->createRdv($manager, $demo, $medecins[2], $lundi->modify('-30 days')->setTime(15, 30), 'Électrocardiogramme de contrôle');
        $this->createRdv($manager, $demo, $medecins[0], $lundiProchain->setTime(9, 30), 'Renouvellement d\'ordonnance');
        $this->createRdv($manager, $demo, $medecins[3], $lundiProchain->modify('+2 days')->setTime(14, 0), 'Contrôle grain de beauté');

        // Créneaux déjà pris par un autre patient : la grille de réservation n'est pas vide
        foreach ([[0, 0, 8, 0], [0, 1, 10, 30], [1, 1, 9, 0], [1, 3, 16, 0], [2, 2, 11, 0], [3, 4, 13, 30]] as [$m, $jour, $h, $min]) {
            $this->createRdv($manager, $autre, $medecins[$m], $lundiProchain->modify("+$jour days")->setTime($h, $min), 'Consultation');
        }

        $manager->flush();
    }

    /**
     * @return array<string, Medicament>
     */
    private function loadPharmacie(ObjectManager $manager): array
    {
        $categories = [];
        foreach ([
            'Antalgiques' => 'Médicaments contre la douleur et la fièvre, pour soulager rapidement les maux du quotidien.',
            'Anti-inflammatoires' => 'Pour réduire l\'inflammation, les douleurs articulaires et musculaires.',
            'Antibiotiques' => 'Traitements des infections bactériennes, délivrés sur présentation d\'une ordonnance.',
        ] as $nom => $description) {
            $categories[$nom] = (new Categorie())->setNom($nom)->setDescription($description);
            $manager->persist($categories[$nom]);
        }

        $data = [
            ['Paracétamol', 'Comprimé', 1000, '2.18', 120, 'paracetamol', 'Antalgiques',
                'Antalgique et antipyrétique de référence. Soulage les douleurs légères à modérées et fait baisser la fièvre. Ne pas dépasser 3 g par jour sans avis médical.'],
            ['Codéine', 'Comprimé', 30, '4.90', 8, 'codeine', 'Antalgiques',
                'Antalgique opioïde faible associé au paracétamol, réservé aux douleurs modérées à intenses ne cédant pas aux antalgiques simples. Sur ordonnance.'],
            ['Ibuprofène', 'Comprimé pelliculé', 400, '3.25', 85, 'ibuprofene', 'Anti-inflammatoires',
                'Anti-inflammatoire non stéroïdien (AINS) indiqué contre les douleurs, la fièvre et les états inflammatoires. À prendre au cours d\'un repas.'],
            ['Naproxène', 'Comprimé', 550, '4.15', 40, 'naproxene', 'Anti-inflammatoires',
                'AINS à action prolongée, efficace sur les douleurs articulaires, tendinites et règles douloureuses.'],
            ['Amoxicilline', 'Gélule', 500, '5.60', 60, 'amoxicilline', 'Antibiotiques',
                'Antibiotique de la famille des pénicillines, utilisé contre de nombreuses infections ORL, respiratoires et urinaires. Sur ordonnance.'],
            ['Clarithromycine', 'Comprimé pelliculé', 500, '7.95', 25, 'clarithromycine', 'Antibiotiques',
                'Antibiotique de la famille des macrolides, alternative en cas d\'allergie aux pénicillines. Sur ordonnance.'],
        ];

        $medicaments = [];
        foreach ($data as [$nom, $forme, $dosage, $prix, $stock, $image, $categorie, $description]) {
            $medicaments[$nom] = (new Medicament())
                ->setNom($nom)
                ->setForme($forme)
                ->setDosage($dosage)
                ->setPrix($prix)
                ->setStock($stock)
                ->setImage('img/meds/'.$image.'.jpg')
                ->setDescription($description)
                ->setCategorie($categories[$categorie]);
            $manager->persist($medicaments[$nom]);
        }

        return $medicaments;
    }

    /**
     * @return list<Medecin>
     */
    private function loadMedecins(ObjectManager $manager): array
    {
        $data = [
            ['Philippe', 'Martin', 'Médecine générale', '01 42 00 00 01', 'doctors-1',
                'Fondateur du cabinet, le Dr Martin exerce la médecine générale depuis plus de vingt ans. Il assure le suivi de toute la famille, de la prévention aux pathologies chroniques.'],
            ['Claire', 'Dubois', 'Pédiatrie', '01 42 00 00 02', 'doctors-2',
                'Pédiatre, le Dr Dubois accompagne les enfants de la naissance à l\'adolescence : vaccinations, suivi de croissance et conseils aux parents.'],
            ['Julien', 'Nguyen', 'Cardiologie', '01 42 00 00 03', 'doctors-3',
                'Cardiologue, le Dr Nguyen prend en charge le dépistage et le suivi des maladies cardiovasculaires, ECG et bilans d\'effort.'],
            ['Léa', 'Moreau', 'Dermatologie', '01 42 00 00 04', 'doctors-4',
                'Dermatologue, le Dr Moreau traite les affections de la peau, des cheveux et des ongles et réalise le dépistage des cancers cutanés.'],
        ];

        $medecins = [];
        foreach ($data as $i => [$prenom, $nom, $specialite, $tel, $photo, $description]) {
            $personne = (new Personne())->setPrenom($prenom)->setNom($nom)->setTelephone($tel);

            $medecin = (new Medecin())
                ->setPersonneMedecin($personne)
                ->setSpecMedecin($specialite)
                ->setNumSiretMedecin(sprintf('8123456780%04d', $i + 1))
                ->setPhoto('img/doctors/'.$photo.'.jpg')
                ->setDescription($description);
            $manager->persist($medecin);
            $medecins[] = $medecin;
        }

        // Compte médecin pour le Dr Martin
        $user = (new User())
            ->setEmail('dr.martin@cabinet-martin.fr')
            ->setRoles(['ROLE_MEDECIN'])
            ->setIsVerified(true);
        $user->setPassword($this->hasher->hashPassword($user, self::DEMO_PASSWORD));
        $medecins[0]->getPersonneMedecin()->setPersonneUser($user);
        $manager->persist($user);

        return $medecins;
    }

    private function createPatient(ObjectManager $manager, string $email, string $prenom, string $nom, string $tel): Patient
    {
        $user = (new User())->setEmail($email)->setIsVerified(true);
        $user->setPassword($this->hasher->hashPassword($user, self::DEMO_PASSWORD));

        $personne = (new Personne())->setPrenom($prenom)->setNom($nom)->setTelephone($tel);
        $personne->setPersonneUser($user);

        $patient = new Patient();
        $patient->setPersonne($personne);

        $manager->persist($user);
        $manager->persist($personne);
        $manager->persist($patient);

        return $patient;
    }

    /**
     * @param list<array{Medicament, int}> $lignes
     */
    private function createCommande(ObjectManager $manager, Personne $personne, \DateTime $date, array $lignes): void
    {
        $commande = (new Commande())->setDateCommande($date)->setPersonne($personne);

        $total = 0.0;
        foreach ($lignes as [$medicament, $quantite]) {
            $ligne = (new LigneCommande())
                ->setMedicamentLigneCommande($medicament)
                ->setQuantite($quantite)
                ->setPrix($medicament->getPrix());
            $commande->addLignesCommande($ligne);
            $total += (float) $ligne->getSousTotal();
        }

        $commande->setTotal(number_format($total, 2, '.', ''));
        $manager->persist($commande);
    }

    private function createRdv(ObjectManager $manager, Patient $patient, Medecin $medecin, \DateTimeImmutable $debut, string $motif): void
    {
        $manager->persist((new RendezVous())
            ->setPatient($patient)
            ->setMedecin($medecin)
            ->setDateDebutRDV($debut)
            ->setDateFinRDV($debut->modify('+30 minutes'))
            ->setCommentaireRDV($motif));
    }
}
