<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Quiz Timer : ajout time_limit, started_at, status, answers_json
 */
final class Version20260221121910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quiz timer fields: time_limit on quiz, started_at/status/answers_json on quiz_attempts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz ADD time_limit INT DEFAULT 0 NOT NULL');
        $this->addSql("ALTER TABLE quiz_attempts ADD started_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD status VARCHAR(20) DEFAULT 'IN_PROGRESS' NOT NULL, ADD answers_json JSON DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz DROP time_limit');
        $this->addSql('ALTER TABLE quiz_attempts DROP started_at, DROP status, DROP answers_json');
    }
}
