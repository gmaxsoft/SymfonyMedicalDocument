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
            ->addDoctor($doctor)
            ->addDoctor($doctor2);

        $profile2 = (new PatientProfile())
            ->setUser($patient2)
            ->setFirstName('Anna')
            ->setLastName('Nowak')
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

        $history1 = (new MedicalHistory())
            ->setPatientProfile($profile1)
            ->setRecordedBy($doctor)
            ->setTitle('Initial consultation')
            ->setDescription('Mock visit notes — replace with real clinical documentation in production.')
            ->setRecordedAt(new DateTimeImmutable('-10 days'));

        $manager->persist($history1);

        $manager->flush();
    }
}
