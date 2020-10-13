<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200927161008 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doodle_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doodle (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, file_name LONGTEXT NOT NULL, user_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E1CE39A66BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doodle ADD CONSTRAINT FK_E1CE39A66BF700BD FOREIGN KEY (status_id) REFERENCES doodle_status (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle DROP FOREIGN KEY FK_E1CE39A66BF700BD');
        $this->addSql('DROP TABLE doodle');
        $this->addSql('DROP TABLE doodle_status');
    }
}
