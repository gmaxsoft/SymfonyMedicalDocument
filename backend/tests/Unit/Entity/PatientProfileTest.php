<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\PatientProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class PatientProfileTest extends TestCase
{
    public function testIsTreatedByReturnsFalseWhenDoctorNotLinked(): void
    {
        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $reflection = new \ReflectionClass($doctor);
        $idProp = $reflection->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($doctor, 1);

        $profile = new PatientProfile();
        $profile->setFirstName('A')->setLastName('B');

        self::assertFalse($profile->isTreatedBy($doctor));
    }

    public function testIsTreatedByReturnsTrueWhenDoctorLinked(): void
    {
        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $reflection = new \ReflectionClass($doctor);
        $idProp = $reflection->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($doctor, 5);

        $profile = new PatientProfile();
        $profile->setFirstName('A')->setLastName('B');
        $profile->addDoctor($doctor);

        self::assertTrue($profile->isTreatedBy($doctor));
    }

    public function testAddDoctorDoesNotDuplicateSameId(): void
    {
        $doctor = (new User())->setEmail('d@example.com')->setRoles(['ROLE_DOCTOR']);
        $reflection = new \ReflectionClass($doctor);
        $idProp = $reflection->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($doctor, 9);

        $profile = new PatientProfile();
        $profile->addDoctor($doctor);
        $profile->addDoctor($doctor);

        self::assertCount(1, $profile->getDoctors());
    }
}
