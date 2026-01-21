<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126204150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment_types (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointments ADD appointment_type_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A546FBEBB FOREIGN KEY (appointment_type_id) REFERENCES appointment_types (id)');
        $this->addSql('CREATE INDEX IDX_6A41727A546FBEBB ON appointments (appointment_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A546FBEBB');
        $this->addSql('DROP TABLE appointment_types');
        $this->addSql('DROP INDEX IDX_6A41727A546FBEBB ON appointments');
        $this->addSql('ALTER TABLE appointments DROP appointment_type_id');
    }
}
