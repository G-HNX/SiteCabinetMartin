<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210122031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT IDENTITY NOT NULL, nom NVARCHAR(255) NOT NULL, description NVARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE commande (id INT IDENTITY NOT NULL, personne_id INT, date_commande DATETIME2(6) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6EEAA67DA21BD112 ON commande (personne_id)');
        $this->addSql('CREATE TABLE contact (id INT IDENTITY NOT NULL, personne_id INT, nom_contact NVARCHAR(100) NOT NULL, email_contact NVARCHAR(255) NOT NULL, date_contact DATETIME2(6) NOT NULL, motif_contact NVARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4C62E638A21BD112 ON contact (personne_id)');
        $this->addSql('CREATE TABLE ligne_commande (id INT IDENTITY NOT NULL, commande_id INT, qtite_ligne_commande INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id)');
        $this->addSql('CREATE TABLE medecin (id INT IDENTITY NOT NULL, personne_medecin_id INT NOT NULL, num_siret_medecin NVARCHAR(14), spec_medecin NVARCHAR(200), PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BDA53C6FEFA5280 ON medecin (personne_medecin_id) WHERE personne_medecin_id IS NOT NULL');
        $this->addSql('CREATE TABLE medicament (id INT IDENTITY NOT NULL, categorie_id INT, nom NVARCHAR(255) NOT NULL, forme NVARCHAR(255) NOT NULL, dosage INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, description VARCHAR(MAX) NOT NULL, image NVARCHAR(255) NOT NULL, stock INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9A9C723ABCF5E72D ON medicament (categorie_id)');
        $this->addSql('CREATE TABLE patient (id INT IDENTITY NOT NULL, patient_personne_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1ADAD7EB9218EE5D ON patient (patient_personne_id) WHERE patient_personne_id IS NOT NULL');
        $this->addSql('CREATE TABLE personne (id INT IDENTITY NOT NULL, id_pers SMALLINT NOT NULL, nom_pers NVARCHAR(255), prenom_pers NVARCHAR(255), role_pers NVARCHAR(100), email_pers NVARCHAR(255), tel_pers NVARCHAR(10), date_creation DATETIME2(6), PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE rendez_vous (id INT IDENTITY NOT NULL, medecin_id INT NOT NULL, patient_id INT, date_debut_rdv DATETIME2(6), date_fin_rdv DATETIME2(6), commentaire_rdv NVARCHAR(255), disponibilite_rdv BIT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_65E8AA0A4F31A84 ON rendez_vous (medecin_id)');
        $this->addSql('CREATE INDEX IDX_65E8AA0A6B899279 ON rendez_vous (patient_id)');
        $this->addSql('EXEC sp_addextendedproperty N\'MS_Description\', N\'(DC2Type:datetime_immutable)\', N\'SCHEMA\', \'dbo\', N\'TABLE\', \'rendez_vous\', N\'COLUMN\', \'date_debut_rdv\'');
        $this->addSql('EXEC sp_addextendedproperty N\'MS_Description\', N\'(DC2Type:datetime_immutable)\', N\'SCHEMA\', \'dbo\', N\'TABLE\', \'rendez_vous\', N\'COLUMN\', \'date_fin_rdv\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT IDENTITY NOT NULL, body VARCHAR(MAX) NOT NULL, headers VARCHAR(MAX) NOT NULL, queue_name NVARCHAR(190) NOT NULL, created_at DATETIME2(6) NOT NULL, available_at DATETIME2(6) NOT NULL, delivered_at DATETIME2(6), PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('EXEC sp_addextendedproperty N\'MS_Description\', N\'(DC2Type:datetime_immutable)\', N\'SCHEMA\', \'dbo\', N\'TABLE\', \'messenger_messages\', N\'COLUMN\', \'created_at\'');
        $this->addSql('EXEC sp_addextendedproperty N\'MS_Description\', N\'(DC2Type:datetime_immutable)\', N\'SCHEMA\', \'dbo\', N\'TABLE\', \'messenger_messages\', N\'COLUMN\', \'available_at\'');
        $this->addSql('EXEC sp_addextendedproperty N\'MS_Description\', N\'(DC2Type:datetime_immutable)\', N\'SCHEMA\', \'dbo\', N\'TABLE\', \'messenger_messages\', N\'COLUMN\', \'delivered_at\'');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_1BDA53C6FEFA5280 FOREIGN KEY (personne_medecin_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE medicament ADD CONSTRAINT FK_9A9C723ABCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB9218EE5D FOREIGN KEY (patient_personne_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA db_accessadmin');
        $this->addSql('CREATE SCHEMA db_backupoperator');
        $this->addSql('CREATE SCHEMA db_datareader');
        $this->addSql('CREATE SCHEMA db_datawriter');
        $this->addSql('CREATE SCHEMA db_ddladmin');
        $this->addSql('CREATE SCHEMA db_denydatareader');
        $this->addSql('CREATE SCHEMA db_denydatawriter');
        $this->addSql('CREATE SCHEMA db_owner');
        $this->addSql('CREATE SCHEMA db_securityadmin');
        $this->addSql('CREATE SCHEMA dbo');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67DA21BD112');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E638A21BD112');
        $this->addSql('ALTER TABLE ligne_commande DROP CONSTRAINT FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE medecin DROP CONSTRAINT FK_1BDA53C6FEFA5280');
        $this->addSql('ALTER TABLE medicament DROP CONSTRAINT FK_9A9C723ABCF5E72D');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB9218EE5D');
        $this->addSql('ALTER TABLE rendez_vous DROP CONSTRAINT FK_65E8AA0A4F31A84');
        $this->addSql('ALTER TABLE rendez_vous DROP CONSTRAINT FK_65E8AA0A6B899279');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP TABLE medicament');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE personne');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
