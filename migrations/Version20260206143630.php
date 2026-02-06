<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206143630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_correct TINYINT NOT NULL, question_id INT NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE certif (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, issued_by VARCHAR(30) NOT NULL, issue_date DATE NOT NULL, exp_date DATE NOT NULL, cv_id INT NOT NULL, INDEX IDX_EC509872CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, chapter_order INT NOT NULL, status VARCHAR(30) NOT NULL, min_score DOUBLE PRECISION NOT NULL, content VARCHAR(255) NOT NULL, title VARCHAR(30) NOT NULL, course_id INT NOT NULL, INDEX IDX_F981B52E591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, duration INT NOT NULL, validation_score DOUBLE PRECISION NOT NULL, is_active TINYINT NOT NULL, display_order INT NOT NULL, content VARCHAR(255) NOT NULL, creator_id INT NOT NULL, INDEX IDX_169E6FB961220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE enrollement (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, progress INT NOT NULL, score DOUBLE PRECISION DEFAULT NULL, completed_at DATE NOT NULL, student_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_1B002285CB944F1A (student_id), INDEX IDX_1B002285591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, point DOUBLE PRECISION NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, passing_score DOUBLE PRECISION NOT NULL, max_attempts INT NOT NULL, course_id INT NOT NULL, chapter_id INT NOT NULL, supervisor_id INT NOT NULL, UNIQUE INDEX UNIQ_A412FA92591CC992 (course_id), UNIQUE INDEX UNIQ_A412FA92579F4768 (chapter_id), INDEX IDX_A412FA9219E9AC5F (supervisor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_attempts (id INT AUTO_INCREMENT NOT NULL, attempt_nbr INT NOT NULL, score DOUBLE PRECISION NOT NULL, submitted_at DATETIME NOT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_69031E21CB944F1A (student_id), INDEX IDX_69031E21853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE certif ADD CONSTRAINT FK_EC509872CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB961220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9219E9AC5F FOREIGN KEY (supervisor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE certif DROP FOREIGN KEY FK_EC509872CFE419E2');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB961220EA6');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285CB944F1A');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285591CC992');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92579F4768');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9219E9AC5F');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21CB944F1A');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21853CD175');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE certif');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrollement');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempts');
    }
}
