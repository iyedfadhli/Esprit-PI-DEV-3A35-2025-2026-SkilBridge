<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210234416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY `FK_A4F157B699D2AD61`');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT FK_A4F157B699D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY `FK_5BA9AAB099D2AD61`');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB099D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY `FK_56D92B9D6146A8E4`');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT FK_56D92B9D6146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
      
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY FK_A4F157B699D2AD61');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT `FK_A4F157B699D2AD61` FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB099D2AD61');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT `FK_5BA9AAB099D2AD61` FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY FK_56D92B9D6146A8E4');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT `FK_56D92B9D6146A8E4` FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
        
    }
}
