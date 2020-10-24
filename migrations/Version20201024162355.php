<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201024162355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle ADD popularity INT NOT NULL DEFAULT 0, ADD views INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE doodle_status CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle DROP popularity, DROP views');
        $this->addSql('ALTER TABLE doodle_status CHANGE is_active is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
