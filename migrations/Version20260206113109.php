<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206113109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cv_application (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, applied_at DATETIME NOT NULL, cv_id INT NOT NULL, offer_id INT NOT NULL, INDEX IDX_BA25D93CCFE419E2 (cv_id), INDEX IDX_BA25D93C53C674EE (offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE langue (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, niveau VARCHAR(30) NOT NULL, cv_id INT NOT NULL, INDEX IDX_9357758ECFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, offer_type VARCHAR(30) NOT NULL, field VARCHAR(30) NOT NULL, required_level VARCHAR(30) NOT NULL, required_skills LONGTEXT NOT NULL, location VARCHAR(40) NOT NULL, contract_type VARCHAR(40) NOT NULL, duration INT DEFAULT NULL, salary_range DOUBLE PRECISION DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_29D6873EA4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93C53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE langue ADD CONSTRAINT FK_9357758ECFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93CCFE419E2');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93C53C674EE');
        $this->addSql('ALTER TABLE langue DROP FOREIGN KEY FK_9357758ECFE419E2');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA4AEAFEA');
        $this->addSql('DROP TABLE cv_application');
        $this->addSql('DROP TABLE langue');
        $this->addSql('DROP TABLE offer');
    }
}
