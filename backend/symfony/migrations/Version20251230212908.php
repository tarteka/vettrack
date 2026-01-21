<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230212908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95E5B533F9');
        $this->addSql('DROP INDEX IDX_6A2F2F95E5B533F9 ON invoices');
        $this->addSql('ALTER TABLE invoices CHANGE appointment_id medical_record_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95B88E2BB6 FOREIGN KEY (medical_record_id) REFERENCES medical_records (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6A2F2F95B88E2BB6 ON invoices (medical_record_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95B88E2BB6');
        $this->addSql('DROP INDEX IDX_6A2F2F95B88E2BB6 ON invoices');
        $this->addSql('ALTER TABLE invoices CHANGE medical_record_id appointment_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6A2F2F95E5B533F9 ON invoices (appointment_id)');
    }
}
