<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220215800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Stripe payment and signature fields to sponsor_hackathon';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sponsor_hackathon ADD payment_status VARCHAR(20) DEFAULT \'PENDING\' NOT NULL, ADD stripe_session_id VARCHAR(255) DEFAULT NULL, ADD signature_data LONGTEXT DEFAULT NULL, ADD signed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sponsor_hackathon DROP payment_status, DROP stripe_session_id, DROP signature_data, DROP signed_at');
    }
}
