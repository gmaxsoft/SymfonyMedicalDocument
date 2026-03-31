<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MedicalHistory;
use App\Entity\PatientProfile;
use App\Entity\Prescription;
use App\Entity\User;
use App\Enum\PrescriptionStatus;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $doctor = (new User())
            ->setEmail('doctor@example.com')
            ->setRoles(['ROLE_DOCTOR']);
        $doctor->setPassword($this->passwordHasher->hashPassword($doctor, 'password'));

        $doctor2 = (new User())
            ->setEmail('doctor2@example.com')
            ->setRoles(['ROLE_DOCTOR']);
        $doctor2->setPassword($this->passwordHasher->hashPassword($doctor2, 'password'));

        $patient1 = (new User())
            ->setEmail('patient1@example.com')
            ->setRoles(['ROLE_PATIENT']);
        $patient1->setPassword($this->passwordHasher->hashPassword($patient1, 'password'));

        $patient2 = (new User())
            ->setEmail('patient2@example.com')
            ->setRoles(['ROLE_PATIENT']);
        $patient2->setPassword($this->passwordHasher->hashPassword($patient2, 'password'));

        $manager->persist($doctor);
        $manager->persist($doctor2);
        $manager->persist($patient1);
        $manager->persist($patient2);

        $profile1 = (new PatientProfile())
            ->setUser($patient1)
            ->setFirstName('Jan')
            ->setLastName('Kowalski')
            ->setBirthDate(new DateTimeImmutable('1988-03-15'))
            ->addDoctor($doctor)
            ->addDoctor($doctor2);

        $profile2 = (new PatientProfile())
            ->setUser($patient2)
            ->setFirstName('Anna')
            ->setLastName('Nowak')
            ->setBirthDate(new DateTimeImmutable('1995-11-02'))
            ->addDoctor($doctor);

        $manager->persist($profile1);
        $manager->persist($profile2);

        $issued = new DateTimeImmutable('-5 days');
        $valid = $issued->modify('+30 days');

        $rx1 = (new Prescription())
            ->setPatientProfile($profile1)
            ->setIssuedBy($doctor)
            ->setMedications([
                ['name' => 'Paracetamol', 'dosage' => '500 mg', 'instructions' => '1 tablet up to 4x daily'],
            ])
            ->setInstructions('Take after meals. Mock data for development (RODO/GDPR demo).')
            ->setStatus(PrescriptionStatus::ACTIVE)
            ->setIssuedAt($issued)
            ->setValidUntil($valid)
            ->setVerificationToken(Prescription::generateVerificationToken());

        $manager->persist($rx1);

        $issued2 = new DateTimeImmutable('-2 days');
        $valid2 = $issued2->modify('+14 days');

        $rx2 = (new Prescription())
            ->setPatientProfile($profile1)
            ->setIssuedBy($doctor2)
            ->setMedications([
                ['name' => 'Ibuprofen', 'dosage' => '200 mg', 'instructions' => '1 tablet every 8 h if pain persists'],
                ['name' => 'Electrolytes', 'dosage' => '1 sachet', 'instructions' => 'Dissolve in water, after diarrhoea'],
            ])
            ->setInstructions('Short course; discontinue if symptoms worsen (demo data).')
            ->setStatus(PrescriptionStatus::ACTIVE)
            ->setIssuedAt($issued2)
            ->setValidUntil($valid2)
            ->setVerificationToken(Prescription::generateVerificationToken());

        $manager->persist($rx2);

        $issued3 = new DateTimeImmutable('-60 days');
        $valid3 = $issued3->modify('+30 days');

        $rx3 = (new Prescription())
            ->setPatientProfile($profile2)
            ->setIssuedBy($doctor)
            ->setMedications([
                ['name' => 'Amoxicillin', 'dosage' => '500 mg', 'instructions' => '1 capsule 3× daily for 7 days'],
            ])
            ->setInstructions('Complete the full course even if you feel better (demo).')
            ->setStatus(PrescriptionStatus::USED)
            ->setIssuedAt($issued3)
            ->setValidUntil($valid3)
            ->setVerificationToken(Prescription::generateVerificationToken());

        $manager->persist($rx3);

        $rx4 = (new Prescription())
            ->setPatientProfile($profile2)
            ->setIssuedBy($doctor)
            ->setMedications([
                ['name' => 'Vitamin D3', 'dosage' => '2000 IU', 'instructions' => 'Once daily with breakfast'],
            ])
            ->setInstructions('Supplementation during winter months — demo entry.')
            ->setStatus(PrescriptionStatus::ACTIVE)
            ->setIssuedAt(new DateTimeImmutable('-7 days'))
            ->setValidUntil((new DateTimeImmutable('-7 days'))->modify('+90 days'))
            ->setVerificationToken(Prescription::generateVerificationToken());

        $manager->persist($rx4);

        $history1 = (new MedicalHistory())
            ->setPatientProfile($profile1)
            ->setRecordedBy($doctor)
            ->setTitle('Initial consultation')
            ->setDescription('Mock visit notes — replace with real clinical documentation in production.')
            ->setRecordedAt(new DateTimeImmutable('-10 days'));

        $manager->persist($history1);

        $history2 = (new MedicalHistory())
            ->setPatientProfile($profile1)
            ->setRecordedBy($doctor2)
            ->setTitle('Follow-up — blood pressure')
            ->setDescription('Demo: BP 128/82 mmHg, lifestyle counselling documented.')
            ->setRecordedAt(new DateTimeImmutable('-3 days'));

        $manager->persist($history2);

        $history3 = (new MedicalHistory())
            ->setPatientProfile($profile2)
            ->setRecordedBy($doctor)
            ->setTitle('Seasonal allergy visit')
            ->setDescription('Demo: symptomatic rhinitis; education materials provided (fictional).')
            ->setRecordedAt(new DateTimeImmutable('-14 days'));

        $manager->persist($history3);

        $manager->flush();
    }
}
