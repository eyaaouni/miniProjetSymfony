<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409162320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livres (id INT AUTO_INCREMENT NOT NULL, cat_id INT DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, isbn VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, image VARCHAR(255) NOT NULL, resume LONGTEXT NOT NULL, editeur VARCHAR(255) NOT NULL, date_edition DATE NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_927187A4E6ADA943 (cat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE livres ADD CONSTRAINT FK_927187A4E6ADA943 FOREIGN KEY (cat_id) REFERENCES categorie (id)');
        $this->addSql('DROP TABLE menu');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE livres DROP FOREIGN KEY FK_927187A4E6ADA943');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE livres');
    }
}
