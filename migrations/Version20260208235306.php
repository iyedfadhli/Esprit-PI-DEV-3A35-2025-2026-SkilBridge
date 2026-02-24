<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208235306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student_response (id INT AUTO_INCREMENT NOT NULL, is_correct TINYINT NOT NULL, points_earned DOUBLE PRECISION NOT NULL, attempt_id INT NOT NULL, question_id INT NOT NULL, selected_answer_id INT NOT NULL, INDEX IDX_8DF04760B191BE6B (attempt_id), INDEX IDX_8DF047601E27F6BF (question_id), INDEX IDX_8DF04760F24C5BEC (selected_answer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760B191BE6B FOREIGN KEY (attempt_id) REFERENCES quiz_attempts (id)');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF047601E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760F24C5BEC FOREIGN KEY (selected_answer_id) REFERENCES answer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760B191BE6B');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF047601E27F6BF');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760F24C5BEC');
        $this->addSql('DROP TABLE student_response');
    }
}
