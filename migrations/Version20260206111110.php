<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206111110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cv (id INT AUTO_INCREMENT NOT NULL, nom_cv VARCHAR(30) NOT NULL, langue VARCHAR(30) NOT NULL, id_template INT DEFAULT NULL, progression INT DEFAULT NULL, creation_date DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, linkedin_url VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_B66FFE92A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE education (id INT AUTO_INCREMENT NOT NULL, degree VARCHAR(30) NOT NULL, field_of_study VARCHAR(40) DEFAULT NULL, school VARCHAR(30) NOT NULL, city VARCHAR(40) DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, description LONGTEXT DEFAULT NULL, cv_id INT NOT NULL, INDEX IDX_DB0A5ED2CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, job_title VARCHAR(30) NOT NULL, company VARCHAR(30) NOT NULL, location VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, currently_working TINYINT NOT NULL, description LONGTEXT NOT NULL, cv_id INT NOT NULL, INDEX IDX_590C103CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, payment_status VARCHAR(30) NOT NULL, payment_ref VARCHAR(30) NOT NULL, registred_at DATETIME NOT NULL, hackathon_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_AB55E24F996D90CF (hackathon_id), INDEX IDX_AB55E24F2F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(35) NOT NULL, type VARCHAR(20) NOT NULL, level VARCHAR(30) NOT NULL, cv_id INT NOT NULL, INDEX IDX_5E3DE477CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor_hackathon (id INT AUTO_INCREMENT NOT NULL, contribution_type VARCHAR(30) NOT NULL, contribution_value DOUBLE PRECISION DEFAULT NULL, sponsor_id INT NOT NULL, hackathon_id INT NOT NULL, INDEX IDX_AECDBCA112F7FB51 (sponsor_id), INDEX IDX_AECDBCA1996D90CF (hackathon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA112F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id)');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA1996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92A76ED395');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2CFE419E2');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103CFE419E2');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F996D90CF');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F2F68B530');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477CFE419E2');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA112F7FB51');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA1996D90CF');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE education');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE sponsor_hackathon');
    }
}
