<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210105115200 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doodle_comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, doodle_id INT NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_8FB32C30A76ED395 (user_id), INDEX IDX_8FB32C30727ACA70 (parent_id), INDEX IDX_8FB32C30C9EC860E (doodle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doodle_comment ADD CONSTRAINT FK_8FB32C30A76ED395 FOREIGN KEY (user_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE doodle_comment ADD CONSTRAINT FK_8FB32C30727ACA70 FOREIGN KEY (parent_id) REFERENCES doodle_comment (id)');
        $this->addSql('ALTER TABLE doodle_comment ADD CONSTRAINT FK_8FB32C30C9EC860E FOREIGN KEY (doodle_id) REFERENCES doodle (id)');
        $this->addSql('ALTER TABLE doodle RENAME INDEX idx_e1ce39a69d86650f TO IDX_E1CE39A6A76ED395');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle_comment DROP FOREIGN KEY FK_8FB32C30727ACA70');
        $this->addSql('DROP TABLE doodle_comment');
        $this->addSql('ALTER TABLE doodle RENAME INDEX idx_e1ce39a6a76ed395 TO IDX_E1CE39A69D86650F');
    }
}
