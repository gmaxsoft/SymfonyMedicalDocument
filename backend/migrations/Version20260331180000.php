<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Clinic model: many doctors per patient via patient_profile_doctor (replaces single assigned_doctor_id).
 */
final class Version20260331180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace patient_profile.assigned_doctor_id with patient_profile_doctor join table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE patient_profile_doctor (patient_profile_id INT NOT NULL, doctor_id INT NOT NULL, PRIMARY KEY(patient_profile_id, doctor_id))');
        $this->addSql('CREATE INDEX IDX_PPD_DOCTOR ON patient_profile_doctor (doctor_id)');
        $this->addSql('ALTER TABLE patient_profile_doctor ADD CONSTRAINT FK_PPD_PROFILE FOREIGN KEY (patient_profile_id) REFERENCES patient_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient_profile_doctor ADD CONSTRAINT FK_PPD_DOCTOR FOREIGN KEY (doctor_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO patient_profile_doctor (patient_profile_id, doctor_id) SELECT id, assigned_doctor_id FROM patient_profile WHERE assigned_doctor_id IS NOT NULL');
        $this->addSql('ALTER TABLE patient_profile DROP CONSTRAINT FK_DC34FFE4FE554EB5');
        $this->addSql('DROP INDEX IDX_DC34FFE4FE554EB5');
        $this->addSql('ALTER TABLE patient_profile DROP assigned_doctor_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient_profile ADD assigned_doctor_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_DC34FFE4FE554EB5 ON patient_profile (assigned_doctor_id)');
        $this->addSql('ALTER TABLE patient_profile ADD CONSTRAINT FK_DC34FFE4FE554EB5 FOREIGN KEY (assigned_doctor_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('UPDATE patient_profile AS p SET assigned_doctor_id = x.doctor_id FROM (SELECT DISTINCT ON (patient_profile_id) patient_profile_id, doctor_id FROM patient_profile_doctor ORDER BY patient_profile_id, doctor_id) AS x WHERE p.id = x.patient_profile_id');
        $this->addSql('ALTER TABLE patient_profile_doctor DROP CONSTRAINT FK_PPD_DOCTOR');
        $this->addSql('ALTER TABLE patient_profile_doctor DROP CONSTRAINT FK_PPD_PROFILE');
        $this->addSql('DROP TABLE patient_profile_doctor');
    }
}
