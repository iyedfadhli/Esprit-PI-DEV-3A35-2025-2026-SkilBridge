<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226160335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP INDEX UNIQ_AC74095A2F68B530, ADD INDEX IDX_AC74095A2F68B530 (group_id_id)');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY `FK_AC74095A2F68B530`');
        $this->addSql('ALTER TABLE activity ADD status VARCHAR(30) DEFAULT NULL, CHANGE submission_file submission_file VARCHAR(255) DEFAULT NULL, CHANGE submission_date submission_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY `FK_B66FFE92A76ED395`');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY `FK_1323A5756146A8E4`');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5756146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE member_activity CHANGE indiv_score indiv_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY `FK_885DBAFA2F68B530`');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE problem_solution CHANGE supervisor_solution supervisor_solution LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reactions DROP INDEX UNIQ_38737FB39D86650F, ADD INDEX IDX_38737FB39D86650F (user_id_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_38737FB39D86650F`');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_post_type ON reactions (user_id_id, post_id_id, type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP INDEX IDX_AC74095A2F68B530, ADD UNIQUE INDEX UNIQ_AC74095A2F68B530 (group_id_id)');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A2F68B530');
        $this->addSql('ALTER TABLE activity DROP status, CHANGE submission_file submission_file VARCHAR(255) NOT NULL, CHANGE submission_date submission_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT `FK_AC74095A2F68B530` FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92A76ED395');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT `FK_B66FFE92A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5756146A8E4');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT `FK_1323A5756146A8E4` FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity CHANGE indiv_score indiv_score DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA2F68B530');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT `FK_885DBAFA2F68B530` FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE problem_solution CHANGE supervisor_solution supervisor_solution LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE reactions DROP INDEX IDX_38737FB39D86650F, ADD UNIQUE INDEX UNIQ_38737FB39D86650F (user_id_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB39D86650F');
        $this->addSql('DROP INDEX uniq_user_post_type ON reactions');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_38737FB39D86650F` FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
