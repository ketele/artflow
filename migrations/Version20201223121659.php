<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223121659 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` ADD created_at INT NOT NULL');
        $this->addSql('ALTER TABLE doodle ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doodle ADD CONSTRAINT FK_E1CE39A69D86650F FOREIGN KEY (user_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_E1CE39A69D86650F ON doodle (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` DROP created_at');
        $this->addSql('ALTER TABLE doodle DROP FOREIGN KEY FK_E1CE39A69D86650F');
        $this->addSql('DROP INDEX IDX_E1CE39A69D86650F ON doodle');
        $this->addSql('ALTER TABLE doodle DROP user_id');
    }
}
