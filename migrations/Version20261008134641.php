<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008134641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, personne_id INT DEFAULT NULL, date_commande DATETIME NOT NULL, total NUMERIC(10, 2) NOT NULL, INDEX IDX_6EEAA67DA21BD112 (personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, personne_id INT DEFAULT NULL, nom_contact VARCHAR(100) NOT NULL, email_contact VARCHAR(255) NOT NULL, date_contact DATETIME NOT NULL, motif_contact VARCHAR(255) NOT NULL, INDEX IDX_4C62E638A21BD112 (personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, medicament_ligne_commande_id INT DEFAULT NULL, quantite INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74B8C5035D0 (medicament_ligne_commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, personne_medecin_id INT NOT NULL, num_siret_medecin VARCHAR(14) DEFAULT NULL, spec_medecin VARCHAR(200) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_1BDA53C6FEFA5280 (personne_medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, forme VARCHAR(255) NOT NULL, dosage INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, stock INT NOT NULL, INDEX IDX_9A9C723ABCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier_item (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, medicament_id INT NOT NULL, quantite INT NOT NULL, UNIQUE INDEX UNIQ_EBFD0067A76ED395AB0D61F7 (user_id, medicament_id), INDEX IDX_EBFD0067A76ED395 (user_id), INDEX IDX_EBFD0067AB0D61F7 (medicament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, personne_id INT NOT NULL, UNIQUE INDEX UNIQ_1ADAD7EBA21BD112 (personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personne (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, telephone VARCHAR(15) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, patient_id INT NOT NULL, date_debut_rdv DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_fin_rdv DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', commentaire_rdv VARCHAR(255) DEFAULT NULL, UNIQUE INDEX uniq_rdv_medecin_debut (medecin_id, date_debut_rdv), INDEX IDX_65E8AA0A4F31A84 (medecin_id), INDEX IDX_65E8AA0A6B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, personne_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649A21BD112 (personne_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B8C5035D0 FOREIGN KEY (medicament_ligne_commande_id) REFERENCES medicament (id)');
        $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_1BDA53C6FEFA5280 FOREIGN KEY (personne_medecin_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE medicament ADD CONSTRAINT FK_9A9C723ABCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE panier_item ADD CONSTRAINT FK_EBFD0067A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE panier_item ADD CONSTRAINT FK_EBFD0067AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBA21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA21BD112');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638A21BD112');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B8C5035D0');
        $this->addSql('ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C6FEFA5280');
        $this->addSql('ALTER TABLE medicament DROP FOREIGN KEY FK_9A9C723ABCF5E72D');
        $this->addSql('ALTER TABLE panier_item DROP FOREIGN KEY FK_EBFD0067A76ED395');
        $this->addSql('ALTER TABLE panier_item DROP FOREIGN KEY FK_EBFD0067AB0D61F7');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBA21BD112');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649A21BD112');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP TABLE medicament');
        $this->addSql('DROP TABLE panier_item');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE personne');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
