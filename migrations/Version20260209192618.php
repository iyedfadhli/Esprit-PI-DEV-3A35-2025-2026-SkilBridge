<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209192618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hackathon ADD pdf_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY `FK_AECDBCA112F7FB51`');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY `FK_AECDBCA1996D90CF`');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA112F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id)');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA1996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hackathon DROP pdf_url');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA112F7FB51');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA1996D90CF');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT `FK_AECDBCA112F7FB51` FOREIGN KEY (sponsor_id) REFERENCES sponsor (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT `FK_AECDBCA1996D90CF` FOREIGN KEY (hackathon_id) REFERENCES hackathon (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
