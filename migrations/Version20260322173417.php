<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322173417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande ADD medicament_ligne_commande_id INT');
        $this->addSql('ALTER TABLE ligne_commande ADD quantite INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD prix NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B8C5035D0 FOREIGN KEY (medicament_ligne_commande_id) REFERENCES medicament (id)');
        $this->addSql('CREATE INDEX IDX_3170B74B8C5035D0 ON ligne_commande (medicament_ligne_commande_id)');
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
        $this->addSql('ALTER TABLE ligne_commande DROP CONSTRAINT FK_3170B74B8C5035D0');
        $this->addSql('DROP INDEX IDX_3170B74B8C5035D0 ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande DROP COLUMN medicament_ligne_commande_id');
        $this->addSql('ALTER TABLE ligne_commande DROP COLUMN quantite');
        $this->addSql('ALTER TABLE ligne_commande DROP COLUMN prix');
    }
}
