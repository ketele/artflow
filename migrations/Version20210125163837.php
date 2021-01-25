<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210125163837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, status_id INT NOT NULL, title VARCHAR(255) NOT NULL, created_at INT NOT NULL, INDEX IDX_527EDB25A76ED395 (user_id), INDEX IDX_527EDB256BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_status (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_40A9E1CFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB256BF700BD FOREIGN KEY (status_id) REFERENCES task_status (id)');
        $this->addSql('ALTER TABLE task_status ADD CONSTRAINT FK_40A9E1CFA76ED395 FOREIGN KEY (user_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE `admin` CHANGE locale locale VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE doodle CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('INSERT INTO `task_status` (`id`, `name`) VALUES (1, \'To do\')');
        $this->addSql('INSERT INTO `task_status` (`id`, `name`) VALUES (2, \'In progress\')');
        $this->addSql('INSERT INTO `task_status` (`id`, `name`) VALUES (3, \'Done\')');
        $this->addSql('ALTER TABLE `task` ALTER status_id SET DEFAULT 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB256BF700BD');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_status');
        $this->addSql('ALTER TABLE `admin` CHANGE locale locale VARCHAR(3) CHARACTER SET utf8mb4 DEFAULT \'en\' NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE doodle CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
