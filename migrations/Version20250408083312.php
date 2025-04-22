<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408083312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE livres ADD cat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE livres ADD CONSTRAINT FK_927187A4E6ADA943 FOREIGN KEY (cat_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_927187A4E6ADA943 ON livres (cat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livres DROP FOREIGN KEY FK_927187A4E6ADA943');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP INDEX IDX_927187A4E6ADA943 ON livres');
        $this->addSql('ALTER TABLE livres DROP cat_id');
    }
}
