<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102152700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_slots DROP FOREIGN KEY FK_2404043E804C8213');
        $this->addSql('DROP INDEX IDX_2404043E804C8213 ON appointment_slots');
        $this->addSql('ALTER TABLE appointment_slots DROP veterinarian_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_slots ADD veterinarian_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE appointment_slots ADD CONSTRAINT FK_2404043E804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2404043E804C8213 ON appointment_slots (veterinarian_id)');
    }
}
