<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204011927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, submission_file VARCHAR(255) NOT NULL, submission_date DATETIME NOT NULL, id_challenge_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_AC74095ABB636FB4 (id_challenge_id), UNIQUE INDEX UNIQ_AC74095A2F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE challenge (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, target_skill VARCHAR(30) NOT NULL, difficulty VARCHAR(30) NOT NULL, min_group_nbr INT NOT NULL, max_group_nbr INT NOT NULL, dead_line DATETIME NOT NULL, created_at DATETIME NOT NULL, creator_id INT NOT NULL, INDEX IDX_D709895161220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, group_score DOUBLE PRECISION DEFAULT NULL, feedback VARCHAR(255) DEFAULT NULL, pre_feedback VARCHAR(255) DEFAULT NULL, activity_id_id INT NOT NULL, UNIQUE INDEX UNIQ_1323A5756146A8E4 (activity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE lessons_learned (id INT AUTO_INCREMENT NOT NULL, lesson_description LONGTEXT NOT NULL, id_activity_id INT NOT NULL, INDEX IDX_A4F157B699D2AD61 (id_activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE member_activity (id INT AUTO_INCREMENT NOT NULL, activity_description LONGTEXT NOT NULL, indiv_score DOUBLE PRECISION NOT NULL, id_activity_id INT NOT NULL, user_id_id INT NOT NULL, INDEX IDX_5BA9AAB099D2AD61 (id_activity_id), INDEX IDX_5BA9AAB09D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE problem_solution (id INT AUTO_INCREMENT NOT NULL, problem_description LONGTEXT NOT NULL, group_solution LONGTEXT DEFAULT NULL, supervisor_solution LONGTEXT NOT NULL, activity_id_id INT NOT NULL, INDEX IDX_56D92B9D6146A8E4 (activity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095ABB636FB4 FOREIGN KEY (id_challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D709895161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5756146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT FK_A4F157B699D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB099D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB09D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT FK_56D92B9D6146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095ABB636FB4');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A2F68B530');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D709895161220EA6');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5756146A8E4');
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY FK_A4F157B699D2AD61');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB099D2AD61');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB09D86650F');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY FK_56D92B9D6146A8E4');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE challenge');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE lessons_learned');
        $this->addSql('DROP TABLE member_activity');
        $this->addSql('DROP TABLE problem_solution');
    }
}
