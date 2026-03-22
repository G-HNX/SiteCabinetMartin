<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322174136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE panier_item (id INT IDENTITY NOT NULL, user_id INT NOT NULL, medicament_id INT NOT NULL, quantite INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_EBFD0067A76ED395 ON panier_item (user_id)');
        $this->addSql('CREATE INDEX IDX_EBFD0067AB0D61F7 ON panier_item (medicament_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EBFD0067A76ED395AB0D61F7 ON panier_item (user_id, medicament_id) WHERE user_id IS NOT NULL AND medicament_id IS NOT NULL');
        $this->addSql('ALTER TABLE panier_item ADD CONSTRAINT FK_EBFD0067A76ED395 FOREIGN KEY (user_id) REFERENCES [user] (id)');
        $this->addSql('ALTER TABLE panier_item ADD CONSTRAINT FK_EBFD0067AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
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
        $this->addSql('ALTER TABLE panier_item DROP CONSTRAINT FK_EBFD0067A76ED395');
        $this->addSql('ALTER TABLE panier_item DROP CONSTRAINT FK_EBFD0067AB0D61F7');
        $this->addSql('DROP TABLE panier_item');
    }
}
