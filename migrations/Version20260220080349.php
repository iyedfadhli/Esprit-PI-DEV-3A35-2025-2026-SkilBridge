<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220080349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY `FK_1323A5756146A8E4`');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5756146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5756146A8E4');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT `FK_1323A5756146A8E4` FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
    }
}
