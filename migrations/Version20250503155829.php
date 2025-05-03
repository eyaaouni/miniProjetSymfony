<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503155829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE livres DROP FOREIGN KEY FK_927187A4E6ADA943');
        $this->addSql('DROP INDEX IDX_927187A4E6ADA943 ON livres');
        $this->addSql('ALTER TABLE livres ADD categorie_id INT NOT NULL, DROP cat_id, CHANGE titre titre VARCHAR(255) NOT NULL, CHANGE isbn isbn VARCHAR(255) NOT NULL, CHANGE slug slug VARCHAR(255) NOT NULL, CHANGE resume resume LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE livres ADD CONSTRAINT FK_927187A4BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_927187A4BCF5E72D ON livres (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE livres DROP FOREIGN KEY FK_927187A4BCF5E72D');
        $this->addSql('DROP INDEX IDX_927187A4BCF5E72D ON livres');
        $this->addSql('ALTER TABLE livres ADD cat_id INT DEFAULT NULL, DROP categorie_id, CHANGE titre titre VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(255) DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL, CHANGE resume resume LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE livres ADD CONSTRAINT FK_927187A4E6ADA943 FOREIGN KEY (cat_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_927187A4E6ADA943 ON livres (cat_id)');
    }
}
