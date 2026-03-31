<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\MedicalHistory;
use App\Entity\PatientProfile;
use App\Entity\User;
use App\Security\Voter\MedicalHistoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class MedicalHistoryVoterTest extends TestCase
{
    public function testCreateGrantedForDoctor(): void
    {
        $voter = new MedicalHistoryVoter();
        $user = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $token = $this->createToken($user);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, null, [MedicalHistoryVoter::CREATE]));
    }

    public function testViewGrantedForTreatingDoctor(): void
    {
        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($doctor, 2);

        $patient = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($patient, 1);

        $profile = new PatientProfile();
        $profile->setUser($patient);
        $profile->setFirstName('A')->setLastName('B');
        $profile->addDoctor($doctor);

        $history = new MedicalHistory();
        $history->setPatientProfile($profile);
        $history->setRecordedBy($doctor);
        $history->setTitle('t');
        $history->setDescription('d');
        $history->setRecordedAt(new \DateTimeImmutable());

        $voter = new MedicalHistoryVoter();
        $token = $this->createToken($doctor);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $history, [MedicalHistoryVoter::VIEW]));
    }

    public function testViewGrantedForPatientOwner(): void
    {
        $patient = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($patient, 7);

        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($doctor, 8);

        $profile = new PatientProfile();
        $profile->setUser($patient);
        $profile->setFirstName('A')->setLastName('B');
        $profile->addDoctor($doctor);

        $history = new MedicalHistory();
        $history->setPatientProfile($profile);
        $history->setRecordedBy($doctor);
        $history->setTitle('t');
        $history->setDescription('d');
        $history->setRecordedAt(new \DateTimeImmutable());

        $voter = new MedicalHistoryVoter();
        $token = $this->createToken($patient);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $history, [MedicalHistoryVoter::VIEW]));
    }

    public function testViewDeniedForDoctorNotOnRoster(): void
    {
        $patient = (new User())->setEmail('p@example.com')->setRoles(['ROLE_PATIENT']);
        $this->setUserId($patient, 1);

        $treatingDoctor = (new User())->setEmail('t@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($treatingDoctor, 2);

        $otherDoctor = (new User())->setEmail('o@example.com')->setRoles(['ROLE_DOCTOR']);
        $this->setUserId($otherDoctor, 3);

        $profile = new PatientProfile();
        $profile->setUser($patient);
        $profile->setFirstName('A')->setLastName('B');
        $profile->addDoctor($treatingDoctor);

        $history = new MedicalHistory();
        $history->setPatientProfile($profile);
        $history->setRecordedBy($treatingDoctor);
        $history->setTitle('t');
        $history->setDescription('d');
        $history->setRecordedAt(new \DateTimeImmutable());

        $voter = new MedicalHistoryVoter();
        $token = $this->createToken($otherDoctor);

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $history, [MedicalHistoryVoter::VIEW]));
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
