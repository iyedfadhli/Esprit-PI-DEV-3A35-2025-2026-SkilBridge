<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206145904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add content column to challenge table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE challenge ADD content VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE challenge DROP content');
    }
}
