<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212164133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY `FK_8DF04760F24C5BEC`');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760F24C5BEC FOREIGN KEY (selected_answer_id) REFERENCES answer (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760F24C5BEC');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT `FK_8DF04760F24C5BEC` FOREIGN KEY (selected_answer_id) REFERENCES answer (id)');
    }
}
