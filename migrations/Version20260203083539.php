<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203083539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, description LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, type VARCHAR(20) NOT NULL, level VARCHAR(20) NOT NULL, max_members INT NOT NULL, rating_score DOUBLE PRECISION NOT NULL, icon VARCHAR(255) NOT NULL, leader_id_id INT NOT NULL, INDEX IDX_6DC044C5EFE6DECF (leader_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE membership (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(20) NOT NULL, contribution_score DOUBLE PRECISION NOT NULL, achievement_unlocked VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, user_id_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_86FFD2859D86650F (user_id_id), INDEX IDX_86FFD2852F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5EFE6DECF FOREIGN KEY (leader_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2859D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2852F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5EFE6DECF');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2859D86650F');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2852F68B530');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE membership');
    }
}
