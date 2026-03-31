<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial schema for medical document / e-prescription API (PostgreSQL).
 */
final class Version20260331165436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create app_user, patient_profile, prescription, medical_history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_user (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_EMAIL ON app_user (email)');
        $this->addSql('CREATE TABLE patient_profile (id SERIAL NOT NULL, first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, user_id INT NOT NULL, assigned_doctor_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC34FFE4A76ED395 ON patient_profile (user_id)');
        $this->addSql('CREATE INDEX IDX_DC34FFE4FE554EB5 ON patient_profile (assigned_doctor_id)');
        $this->addSql('ALTER TABLE patient_profile ADD CONSTRAINT FK_DC34FFE4A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient_profile ADD CONSTRAINT FK_DC34FFE4FE554EB5 FOREIGN KEY (assigned_doctor_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE prescription (id SERIAL NOT NULL, medications JSON NOT NULL, instructions TEXT NOT NULL, status VARCHAR(255) NOT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, verification_token VARCHAR(36) NOT NULL, patient_profile_id INT NOT NULL, issued_by_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D9C1CC006B ON prescription (verification_token)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D97A3AB457 ON prescription (patient_profile_id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D9784BB717 ON prescription (issued_by_id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D97A3AB457 FOREIGN KEY (patient_profile_id) REFERENCES patient_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9784BB717 FOREIGN KEY (issued_by_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE medical_history (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, patient_profile_id INT NOT NULL, recorded_by_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_61B890857A3AB457 ON medical_history (patient_profile_id)');
        $this->addSql('CREATE INDEX IDX_61B89085D05A957B ON medical_history (recorded_by_id)');
        $this->addSql('ALTER TABLE medical_history ADD CONSTRAINT FK_61B890857A3AB457 FOREIGN KEY (patient_profile_id) REFERENCES patient_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE medical_history ADD CONSTRAINT FK_61B89085D05A957B FOREIGN KEY (recorded_by_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_history DROP CONSTRAINT FK_61B89085D05A957B');
        $this->addSql('ALTER TABLE medical_history DROP CONSTRAINT FK_61B890857A3AB457');
        $this->addSql('DROP TABLE medical_history');
        $this->addSql('ALTER TABLE prescription DROP CONSTRAINT FK_1FBFB8D9784BB717');
        $this->addSql('ALTER TABLE prescription DROP CONSTRAINT FK_1FBFB8D97A3AB457');
        $this->addSql('DROP TABLE prescription');
        $this->addSql('ALTER TABLE patient_profile DROP CONSTRAINT FK_DC34FFE4FE554EB5');
        $this->addSql('ALTER TABLE patient_profile DROP CONSTRAINT FK_DC34FFE4A76ED395');
        $this->addSql('DROP TABLE patient_profile');
        $this->addSql('DROP TABLE app_user');
    }
}
