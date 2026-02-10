<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206103252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, likes INT NOT NULL, author_id_id INT NOT NULL, INDEX IDX_D9BEC0C469CCBE9A (author_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE hackathon (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, theme VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, rules LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, registration_open_at DATETIME NOT NULL, registration_close_at DATETIME NOT NULL, fee DOUBLE PRECISION NOT NULL, max_teams INT NOT NULL, team_size_max INT NOT NULL, location VARCHAR(255) NOT NULL, cover_url VARCHAR(255) NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, creator_id_id INT NOT NULL, INDEX IDX_8B3AF64FF05788E9 (creator_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, titre VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, visibility VARCHAR(30) NOT NULL, attached_file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, likes_counter INT NOT NULL, group_id_id INT NOT NULL, author_id_id INT NOT NULL, INDEX IDX_885DBAFA2F68B530 (group_id_id), INDEX IDX_885DBAFA69CCBE9A (author_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reactions (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(25) NOT NULL, url VARCHAR(255) NOT NULL, posted_at DATETIME NOT NULL, user_id_id INT NOT NULL, post_id_id INT NOT NULL, UNIQUE INDEX UNIQ_38737FB39D86650F (user_id_id), INDEX IDX_38737FB3E85F12B8 (post_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, logo_url VARCHAR(255) NOT NULL, website_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, creator_id_id INT NOT NULL, INDEX IDX_818CC9D4F05788E9 (creator_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C469CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hackathon ADD CONSTRAINT FK_8B3AF64FF05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA69CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3E85F12B8 FOREIGN KEY (post_id_id) REFERENCES posts (id)');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D4F05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C469CCBE9A');
        $this->addSql('ALTER TABLE hackathon DROP FOREIGN KEY FK_8B3AF64FF05788E9');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA2F68B530');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA69CCBE9A');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB39D86650F');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3E85F12B8');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D4F05788E9');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE hackathon');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE reactions');
        $this->addSql('DROP TABLE sponsor');
    }
}
