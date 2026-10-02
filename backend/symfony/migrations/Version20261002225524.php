<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261002225524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_available_slots ON appointment_slots (slot_date, is_available)');
        $this->addSql('CREATE UNIQUE INDEX unique_slot ON appointment_slots (slot_date, slot_time)');
        $this->addSql('ALTER TABLE appointments DROP INDEX IDX_6A41727AC8C623B4, ADD UNIQUE INDEX unique_appointment_slot (appointment_slot_id)');
        $this->addSql('CREATE INDEX idx_status ON appointments (status)');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_6a41727a966f7fb6 TO idx_pet_appointments');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_6a41727a804c8213 TO idx_vet_appointments');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_6a41727ade12ab56 TO idx_created_by');
        $this->addSql('ALTER TABLE invoice_items RENAME INDEX idx_dcc4b9f82989f1fd TO idx_invoice_items');
        $this->addSql('CREATE INDEX idx_invoice_number ON invoices (invoice_number)');
        $this->addSql('CREATE INDEX idx_status ON invoices (status)');
        $this->addSql('ALTER TABLE invoices RENAME INDEX idx_6a2f2f95a76ed395 TO idx_user_invoices');
        $this->addSql('ALTER TABLE invoices RENAME INDEX idx_6a2f2f95966f7fb6 TO idx_pet_invoices');
        $this->addSql('CREATE INDEX idx_record_date ON medical_records (record_date)');
        $this->addSql('ALTER TABLE medical_records RENAME INDEX idx_da9fb888966f7fb6 TO idx_pet_records');
        $this->addSql('ALTER TABLE medical_records RENAME INDEX idx_da9fb888804c8213 TO idx_veterinarian_records');
        $this->addSql('ALTER TABLE pet_types RENAME INDEX uniq_f44c49935e237e06 TO unique_pet_type_name');
        $this->addSql('CREATE INDEX idx_active ON pets (is_active)');
        $this->addSql('ALTER TABLE pets RENAME INDEX idx_8638ea3fa76ed395 TO idx_user_pets');
        $this->addSql('ALTER TABLE pets RENAME INDEX uniq_8638ea3fe83ab044 TO unique_microchip');
        $this->addSql('CREATE INDEX idx_active ON services (is_active)');
        $this->addSql('ALTER TABLE services RENAME INDEX idx_7332e16912469de2 TO idx_category');
        $this->addSql('CREATE INDEX idx_pet_treatments ON treatments (pet_id, status)');
        $this->addSql('CREATE INDEX idx_status ON treatments (status)');
        $this->addSql('CREATE INDEX idx_dates ON treatments (start_date, end_date)');
        $this->addSql('CREATE INDEX idx_email ON users (email)');
        $this->addSql('CREATE INDEX idx_dni ON users (dni)');
        $this->addSql('CREATE INDEX idx_active ON users (is_active, is_verified)');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO unique_email');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e97f8f253b TO unique_dni');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_available_slots ON appointment_slots');
        $this->addSql('DROP INDEX unique_slot ON appointment_slots');
        $this->addSql('ALTER TABLE appointments DROP INDEX unique_appointment_slot, ADD INDEX IDX_6A41727AC8C623B4 (appointment_slot_id)');
        $this->addSql('DROP INDEX idx_status ON appointments');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_pet_appointments TO IDX_6A41727A966F7FB6');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_vet_appointments TO IDX_6A41727A804C8213');
        $this->addSql('ALTER TABLE appointments RENAME INDEX idx_created_by TO IDX_6A41727ADE12AB56');
        $this->addSql('ALTER TABLE invoice_items RENAME INDEX idx_invoice_items TO IDX_DCC4B9F82989F1FD');
        $this->addSql('DROP INDEX idx_invoice_number ON invoices');
        $this->addSql('DROP INDEX idx_status ON invoices');
        $this->addSql('ALTER TABLE invoices RENAME INDEX idx_user_invoices TO IDX_6A2F2F95A76ED395');
        $this->addSql('ALTER TABLE invoices RENAME INDEX idx_pet_invoices TO IDX_6A2F2F95966F7FB6');
        $this->addSql('DROP INDEX idx_record_date ON medical_records');
        $this->addSql('ALTER TABLE medical_records RENAME INDEX idx_veterinarian_records TO IDX_DA9FB888804C8213');
        $this->addSql('ALTER TABLE medical_records RENAME INDEX idx_pet_records TO IDX_DA9FB888966F7FB6');
        $this->addSql('ALTER TABLE pet_types RENAME INDEX unique_pet_type_name TO UNIQ_F44C49935E237E06');
        $this->addSql('DROP INDEX idx_active ON pets');
        $this->addSql('ALTER TABLE pets RENAME INDEX unique_microchip TO UNIQ_8638EA3FE83AB044');
        $this->addSql('ALTER TABLE pets RENAME INDEX idx_user_pets TO IDX_8638EA3FA76ED395');
        $this->addSql('DROP INDEX idx_active ON services');
        $this->addSql('ALTER TABLE services RENAME INDEX idx_category TO IDX_7332E16912469DE2');
        $this->addSql('DROP INDEX idx_pet_treatments ON treatments');
        $this->addSql('DROP INDEX idx_status ON treatments');
        $this->addSql('DROP INDEX idx_dates ON treatments');
        $this->addSql('DROP INDEX idx_email ON users');
        $this->addSql('DROP INDEX idx_dni ON users');
        $this->addSql('DROP INDEX idx_active ON users');
        $this->addSql('ALTER TABLE users RENAME INDEX unique_email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE users RENAME INDEX unique_dni TO UNIQ_1483A5E97F8F253B');
    }
}
