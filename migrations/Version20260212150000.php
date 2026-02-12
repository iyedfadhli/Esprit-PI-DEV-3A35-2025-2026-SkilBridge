<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow multiple reactions per user across posts; add composite unique (user_id_id, post_id_id, type)';
    }

    public function up(Schema $schema): void
    {
        // Drop old unique on user_id_id only (requires dropping FK first)
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB39D86650F');
        $this->addSql('DROP INDEX UNIQ_38737FB39D86650F ON reactions');
        // Add new composite unique to prevent duplicate likes on same post by same user
        $this->addSql('CREATE UNIQUE INDEX uniq_user_post_type ON reactions (user_id_id, post_id_id, type)');
        // Re-add FK on user
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // Revert: drop FK, drop composite unique and restore old unique on user_id_id, then re-add FK
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB39D86650F');
        $this->addSql('DROP INDEX uniq_user_post_type ON reactions');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_38737FB39D86650F ON reactions (user_id_id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }
}
