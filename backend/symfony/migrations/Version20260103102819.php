<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103102819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A804C8213');
        $this->addSql('ALTER TABLE appointments CHANGE veterinarian_id veterinarian_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A804C8213');
        $this->addSql('ALTER TABLE appointments CHANGE veterinarian_id veterinarian_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
