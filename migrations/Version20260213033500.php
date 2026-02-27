<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213033500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add material column to course table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course ADD material VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course DROP material');
    }
}
