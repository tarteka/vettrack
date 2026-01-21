<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223200418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vaccinations DROP FOREIGN KEY FK_92C6ED72966F7FB6');
        $this->addSql('ALTER TABLE vaccinations DROP FOREIGN KEY FK_92C6ED72804C8213');
        $this->addSql('DROP TABLE vaccinations');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vaccinations (id BIGINT AUTO_INCREMENT NOT NULL, pet_id BIGINT NOT NULL, veterinarian_id BIGINT NOT NULL, vaccine_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, vaccination_date DATE NOT NULL, next_vaccination_date DATE DEFAULT NULL, batch_number VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, notes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_92C6ED72966F7FB6 (pet_id), INDEX IDX_92C6ED72804C8213 (veterinarian_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE vaccinations ADD CONSTRAINT FK_92C6ED72966F7FB6 FOREIGN KEY (pet_id) REFERENCES pets (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vaccinations ADD CONSTRAINT FK_92C6ED72804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
