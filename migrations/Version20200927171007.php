<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200927171007 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE doodle_status CHANGE is_active TINYINT(1) DEFAULT \'true\'');
        $this->addSql('ALTER TABLE doodle_status ALTER is_active SET DEFAULT 1');
        $this->addSql('INSERT INTO `doodle_status` (`id`, `name`) VALUES (1, \'Published\')');
        $this->addSql('INSERT INTO `doodle_status` (`id`, `name`) VALUES (2, \'New\')');
        $this->addSql('INSERT INTO `doodle_status` (`id`, `name`) VALUES (3, \'Rejected\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doodle_status CHANGE is_active is_active TINYINT(1) NOT NULL');
    }
}
