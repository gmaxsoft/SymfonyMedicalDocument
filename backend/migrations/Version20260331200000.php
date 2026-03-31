<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add patient_profile.birth_date (date of birth)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient_profile ADD birth_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient_profile DROP birth_date');
    }
}
