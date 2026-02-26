<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create email_verification table for OTP codes
 */
final class Version20260225113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create email_verification table for OTP email verification';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, code VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX IDX_FE22358E7927C74 ON email_verification (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE email_verification');
    }
}
