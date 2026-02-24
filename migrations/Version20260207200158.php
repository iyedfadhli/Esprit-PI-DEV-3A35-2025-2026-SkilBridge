<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207200158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE challenge DROP COLUMN IF EXISTS `content`');
        // FK and index for course_id were added in an earlier migration; skip here
        $this->addSql('ALTER TABLE `commentaires` ADD COLUMN IF NOT EXISTS `post_id` INT NOT NULL');
        // FK and index for post_id may already exist on the database; skip creation here
        $this->addSql('ALTER TABLE `course` DROP COLUMN IF EXISTS `is_active`, DROP COLUMN IF EXISTS `display_order`');
        $this->addSql('ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `report_nbr` INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE prenom prenom VARCHAR(30) DEFAULT NULL, CHANGE date_naissance date_naissance DATE DEFAULT NULL, CHANGE ban ban TINYINT DEFAULT 0 NOT NULL, CHANGE is_active is_active TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // FK and index removal handled by earlier migration; nothing to do here
        $this->addSql('ALTER TABLE challenge ADD content VARCHAR(255) DEFAULT NULL');
        // FK/index removal for post_id handled by earlier migration (if needed)
        $this->addSql('ALTER TABLE `commentaires` DROP COLUMN IF EXISTS `post_id`');
        $this->addSql('ALTER TABLE course ADD is_active TINYINT NOT NULL, ADD display_order INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP report_nbr, CHANGE prenom prenom VARCHAR(30) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE ban ban TINYINT NOT NULL, CHANGE is_active is_active TINYINT NOT NULL');
    }
}
