<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\PatientProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiWebTestCase extends WebTestCase
{
    protected function seedPatientWithProfile(string $email, string $plainPassword): void
    {
        $container = static::getContainer();
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_PATIENT']);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $profile = (new PatientProfile())
            ->setFirstName('Jan')
            ->setLastName('Testowy');
        $user->setPatientProfile($profile);

        $em->persist($user);
        $em->flush();
    }

    protected function seedDoctor(string $email, string $plainPassword): void
    {
        $container = static::getContainer();
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_DOCTOR']);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $em->persist($user);
        $em->flush();
    }

    protected function obtainJwt(KernelBrowser $client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/api/auth',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $password], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        self::assertArrayHasKey('token', $data);
        self::assertIsString($data['token']);

        return $data['token'];
    }
}
