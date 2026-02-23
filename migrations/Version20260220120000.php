<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration : Ajout des champs difficulty et is_active à la table course.
 * Nécessaire pour le système de recommandation intelligente.
 */
final class Version20260220120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes difficulty et is_active à la table course pour le système de recommandation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course ADD difficulty VARCHAR(20) DEFAULT \'BEGINNER\' NOT NULL');
        $this->addSql('ALTER TABLE course ADD is_active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course DROP difficulty');
        $this->addSql('ALTER TABLE course DROP is_active');
    }
}
