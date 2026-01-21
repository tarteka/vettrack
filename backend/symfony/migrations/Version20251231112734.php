<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231112734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_items DROP FOREIGN KEY FK_DCC4B9F8ED5CA9E6');
        $this->addSql('ALTER TABLE invoice_items ADD sub_total NUMERIC(10, 2) NOT NULL, ADD tax_amount NUMERIC(5, 2) NOT NULL, ADD total_amount NUMERIC(5, 2) NOT NULL, DROP description, DROP unit_price, DROP tax_rate, DROP total_price, CHANGE service_id service_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F8ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE invoices DROP tax_rate');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_items DROP FOREIGN KEY FK_DCC4B9F8ED5CA9E6');
        $this->addSql('ALTER TABLE invoice_items ADD description VARCHAR(255) NOT NULL, ADD tax_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL, ADD total_price NUMERIC(10, 2) NOT NULL, DROP tax_amount, DROP total_amount, CHANGE service_id service_id INT DEFAULT NULL, CHANGE sub_total unit_price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F8ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoices ADD tax_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL');
    }
}
