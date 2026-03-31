<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\PatientProfile;
use App\Entity\Prescription;
use App\Entity\User;
use App\Enum\PrescriptionStatus;
use App\Security\Voter\PrescriptionVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class PrescriptionVoterTest extends TestCase
{
    public function testCreateDeniedForPatient(): void
    {
        $voter = new PrescriptionVoter();
        $user = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $token = $this->createToken($user);

        $vote = $voter->vote($token, null, [PrescriptionVoter::CREATE]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $vote);
    }

    public function testCreateGrantedForDoctor(): void
    {
        $voter = new PrescriptionVoter();
        $user = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $token = $this->createToken($user);

        $vote = $voter->vote($token, null, [PrescriptionVoter::CREATE]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testViewGrantedForPatientOwner(): void
    {
        $patient = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($patient, 10);

        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($doctor, 20);

        $profile = new PatientProfile();
        $profile->setUser($patient);
        $profile->setFirstName('X')->setLastName('Y');
        $profile->addDoctor($doctor);

        $prescription = new Prescription();
        $prescription->setPatientProfile($profile);
        $prescription->setIssuedBy($doctor);
        $prescription->setMedications([]);
        $prescription->setInstructions('i');
        $prescription->setStatus(PrescriptionStatus::ACTIVE);
        $prescription->setIssuedAt(new \DateTimeImmutable());
        $prescription->setValidUntil(new \DateTimeImmutable('+1 day'));
        $prescription->setVerificationToken('00000000-0000-4000-8000-000000000001');

        $voter = new PrescriptionVoter();
        $token = $this->createToken($patient);

        $vote = $voter->vote($token, $prescription, [PrescriptionVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testViewDeniedForOtherPatient(): void
    {
        $patient = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($patient, 10);

        $otherPatient = (new User())->setEmail('other@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($otherPatient, 99);

        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($doctor, 20);

        $profile = new PatientProfile();
        $profile->setUser($patient);
        $profile->setFirstName('X')->setLastName('Y');
        $profile->addDoctor($doctor);

        $prescription = new Prescription();
        $prescription->setPatientProfile($profile);
        $prescription->setIssuedBy($doctor);
        $prescription->setMedications([]);
        $prescription->setInstructions('i');
        $prescription->setStatus(PrescriptionStatus::ACTIVE);
        $prescription->setIssuedAt(new \DateTimeImmutable());
        $prescription->setValidUntil(new \DateTimeImmutable('+1 day'));
        $prescription->setVerificationToken('00000000-0000-4000-8000-000000000002');

        $voter = new PrescriptionVoter();
        $token = $this->createToken($otherPatient);

        $vote = $voter->vote($token, $prescription, [PrescriptionVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $vote);
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    private function setUserId(User $user, int $id): void
    {
        $reflection = new \ReflectionClass($user);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($user, $id);
    }
}
