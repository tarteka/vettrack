<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225190744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE treatments ADD name VARCHAR(125) NOT NULL, ADD medicine VARCHAR(125) NOT NULL, ADD dose VARCHAR(50) NOT NULL, ADD frequency VARCHAR(50) NOT NULL, ADD instructions LONGTEXT NOT NULL, DROP description, DROP notes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE treatments ADD description LONGTEXT DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, DROP name, DROP medicine, DROP dose, DROP frequency, DROP instructions');
    }
}
