<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109121015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment_slots (id BIGINT AUTO_INCREMENT NOT NULL, veterinarian_id BIGINT NOT NULL, slot_date DATE NOT NULL, slot_time TIME NOT NULL, duration_minutes INT DEFAULT 30 NOT NULL, is_available TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_2404043E804C8213 (veterinarian_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appointments (id BIGINT AUTO_INCREMENT NOT NULL, pet_id BIGINT NOT NULL, veterinarian_id BIGINT NOT NULL, appointment_slot_id BIGINT NOT NULL, created_by BIGINT DEFAULT NULL, reason LONGTEXT NOT NULL, status VARCHAR(20) DEFAULT \'programada\' NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6A41727A966F7FB6 (pet_id), INDEX IDX_6A41727A804C8213 (veterinarian_id), INDEX IDX_6A41727AC8C623B4 (appointment_slot_id), INDEX IDX_6A41727ADE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clinic_settings (id INT DEFAULT 1 NOT NULL, clinic_name VARCHAR(200) NOT NULL, cif VARCHAR(50) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, province VARCHAR(100) NOT NULL, country VARCHAR(100) DEFAULT \'España\' NOT NULL, phone VARCHAR(20) NOT NULL, email VARCHAR(180) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_items (id BIGINT AUTO_INCREMENT NOT NULL, invoice_id BIGINT NOT NULL, service_id INT DEFAULT NULL, description VARCHAR(255) NOT NULL, quantity INT DEFAULT 1 NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) DEFAULT \'21\' NOT NULL, total_price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DCC4B9F82989F1FD (invoice_id), INDEX IDX_DCC4B9F8ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoices (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, appointment_id BIGINT DEFAULT NULL, created_by BIGINT DEFAULT NULL, invoice_date DATE NOT NULL, invoice_number VARCHAR(9) DEFAULT NULL, due_date DATE DEFAULT NULL, subtotal NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) DEFAULT \'21\' NOT NULL, tax_amount NUMERIC(10, 2) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, status VARCHAR(10) DEFAULT \'pendiente\' NOT NULL, payment_method VARCHAR(20) DEFAULT NULL, payment_date DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6A2F2F95A76ED395 (user_id), INDEX IDX_6A2F2F95E5B533F9 (appointment_id), INDEX IDX_6A2F2F95DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medical_records (id BIGINT AUTO_INCREMENT NOT NULL, pet_id BIGINT NOT NULL, veterinarian_id BIGINT NOT NULL, record_date DATETIME NOT NULL, diagnosis LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DA9FB888966F7FB6 (pet_id), INDEX IDX_DA9FB888804C8213 (veterinarian_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_F44C49935E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pets (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, pet_type_id INT NOT NULL, name VARCHAR(100) NOT NULL, breed VARCHAR(100) DEFAULT NULL, birth_date DATE DEFAULT NULL, gender VARCHAR(10) NOT NULL, microchip VARCHAR(15) DEFAULT NULL, weight INT DEFAULT NULL, alergies LONGTEXT DEFAULT NULL, sterilized TINYINT(1) DEFAULT NULL, insurance_provider VARCHAR(100) DEFAULT NULL, insurance_policy_number VARCHAR(100) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8638EA3FE83AB044 (microchip), INDEX IDX_8638EA3FA76ED395 (user_id), INDEX IDX_8638EA3FDB020C75 (pet_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_ACA27FDC5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, unit_price NUMERIC(10, 2) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7332E16912469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE treatments (id BIGINT AUTO_INCREMENT NOT NULL, medical_record_id BIGINT NOT NULL, pet_id BIGINT NOT NULL, veterinarian_id BIGINT NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status VARCHAR(20) DEFAULT \'activo\' NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4A48CE0DB88E2BB6 (medical_record_id), INDEX IDX_4A48CE0D966F7FB6 (pet_id), INDEX IDX_4A48CE0D804C8213 (veterinarian_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id BIGINT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(150) NOT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(150) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, license_number VARCHAR(50) DEFAULT NULL, specialization VARCHAR(150) DEFAULT NULL, is_active TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, password_changed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9EC7E7152 (license_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vaccinations (id BIGINT AUTO_INCREMENT NOT NULL, pet_id BIGINT NOT NULL, veterinarian_id BIGINT NOT NULL, vaccine_name VARCHAR(100) NOT NULL, vaccination_date DATE NOT NULL, next_vaccination_date DATE DEFAULT NULL, batch_number VARCHAR(50) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_92C6ED72966F7FB6 (pet_id), INDEX IDX_92C6ED72804C8213 (veterinarian_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment_slots ADD CONSTRAINT FK_2404043E804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A966F7FB6 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727AC8C623B4 FOREIGN KEY (appointment_slot_id) REFERENCES appointment_slots (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727ADE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F82989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F8ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE medical_records ADD CONSTRAINT FK_DA9FB888966F7FB6 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE medical_records ADD CONSTRAINT FK_DA9FB888804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE pets ADD CONSTRAINT FK_8638EA3FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pets ADD CONSTRAINT FK_8638EA3FDB020C75 FOREIGN KEY (pet_type_id) REFERENCES pet_types (id)');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E16912469DE2 FOREIGN KEY (category_id) REFERENCES service_categories (id)');
        $this->addSql('ALTER TABLE treatments ADD CONSTRAINT FK_4A48CE0DB88E2BB6 FOREIGN KEY (medical_record_id) REFERENCES medical_records (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE treatments ADD CONSTRAINT FK_4A48CE0D966F7FB6 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE treatments ADD CONSTRAINT FK_4A48CE0D804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE vaccinations ADD CONSTRAINT FK_92C6ED72966F7FB6 FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vaccinations ADD CONSTRAINT FK_92C6ED72804C8213 FOREIGN KEY (veterinarian_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_slots DROP FOREIGN KEY FK_2404043E804C8213');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A966F7FB6');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A804C8213');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727AC8C623B4');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727ADE12AB56');
        $this->addSql('ALTER TABLE invoice_items DROP FOREIGN KEY FK_DCC4B9F82989F1FD');
        $this->addSql('ALTER TABLE invoice_items DROP FOREIGN KEY FK_DCC4B9F8ED5CA9E6');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95A76ED395');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95E5B533F9');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_6A2F2F95DE12AB56');
        $this->addSql('ALTER TABLE medical_records DROP FOREIGN KEY FK_DA9FB888966F7FB6');
        $this->addSql('ALTER TABLE medical_records DROP FOREIGN KEY FK_DA9FB888804C8213');
        $this->addSql('ALTER TABLE pets DROP FOREIGN KEY FK_8638EA3FA76ED395');
        $this->addSql('ALTER TABLE pets DROP FOREIGN KEY FK_8638EA3FDB020C75');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E16912469DE2');
        $this->addSql('ALTER TABLE treatments DROP FOREIGN KEY FK_4A48CE0DB88E2BB6');
        $this->addSql('ALTER TABLE treatments DROP FOREIGN KEY FK_4A48CE0D966F7FB6');
        $this->addSql('ALTER TABLE treatments DROP FOREIGN KEY FK_4A48CE0D804C8213');
        $this->addSql('ALTER TABLE vaccinations DROP FOREIGN KEY FK_92C6ED72966F7FB6');
        $this->addSql('ALTER TABLE vaccinations DROP FOREIGN KEY FK_92C6ED72804C8213');
        $this->addSql('DROP TABLE appointment_slots');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE clinic_settings');
        $this->addSql('DROP TABLE invoice_items');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE medical_records');
        $this->addSql('DROP TABLE pet_types');
        $this->addSql('DROP TABLE pets');
        $this->addSql('DROP TABLE service_categories');
        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE treatments');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE vaccinations');
    }
}
