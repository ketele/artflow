<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200927175539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle ADD coordinates JSON DEFAULT NULL, ADD source_doodle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doodle_status CHANGE is_active is_active TINYINT(1) DEFAULT 1');
        $this->addSql('ALTER TABLE doodle CHANGE status_id status_id INT DEFAULT 2');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle DROP coordinates, DROP source_doodle_id');
        $this->addSql('ALTER TABLE doodle_status CHANGE is_active is_active TINYINT(1) NOT NULL');
    }
}
