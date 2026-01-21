<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125230826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices MODIFY due_date DATE GENERATED ALWAYS AS (DATE_ADD(invoice_date, INTERVAL 1 MONTH)) STORED');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoices MODIFY due_date DATE DEFAULT NULL');
    }
}
