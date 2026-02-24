<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206210658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD sections_to_review JSON DEFAULT NULL, ADD prerequisite_quiz_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9618C39EB FOREIGN KEY (prerequisite_quiz_id) REFERENCES quiz (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9618C39EB ON course (prerequisite_quiz_id)');
        $this->addSql('ALTER TABLE quiz ADD questions_per_attempt INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9618C39EB');
        $this->addSql('DROP INDEX IDX_169E6FB9618C39EB ON course');
        $this->addSql('ALTER TABLE course DROP sections_to_review, DROP prerequisite_quiz_id');
        $this->addSql('ALTER TABLE quiz DROP questions_per_attempt');
    }
}
