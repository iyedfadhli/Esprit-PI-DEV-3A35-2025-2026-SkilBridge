<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207200158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE challenge DROP content');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D7098951591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_D7098951591CC992 ON challenge (course_id)');
        $this->addSql('ALTER TABLE commentaires ADD post_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C44B89032C FOREIGN KEY (post_id) REFERENCES posts (id)');
        $this->addSql('CREATE INDEX IDX_D9BEC0C44B89032C ON commentaires (post_id)');
        $this->addSql('ALTER TABLE course DROP is_active, DROP display_order');
        $this->addSql('ALTER TABLE user ADD report_nbr INT DEFAULT 0 NOT NULL, CHANGE prenom prenom VARCHAR(30) DEFAULT NULL, CHANGE date_naissance date_naissance DATE DEFAULT NULL, CHANGE ban ban TINYINT DEFAULT 0 NOT NULL, CHANGE is_active is_active TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D7098951591CC992');
        $this->addSql('DROP INDEX IDX_D7098951591CC992 ON challenge');
        $this->addSql('ALTER TABLE challenge ADD content VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C44B89032C');
        $this->addSql('DROP INDEX IDX_D9BEC0C44B89032C ON commentaires');
        $this->addSql('ALTER TABLE commentaires DROP post_id');
        $this->addSql('ALTER TABLE course ADD is_active TINYINT NOT NULL, ADD display_order INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP report_nbr, CHANGE prenom prenom VARCHAR(30) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE ban ban TINYINT NOT NULL, CHANGE is_active is_active TINYINT NOT NULL');
    }
}
