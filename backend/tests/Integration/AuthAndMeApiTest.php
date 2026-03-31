<?php

declare(strict_types=1);

namespace App\Tests\Integration;

final class AuthAndMeApiTest extends ApiWebTestCase
{
    public function testPostAuthReturns401ForInvalidCredentials(): void
    {
        $client = static::createClient();
        $this->seedPatientWithProfile('valid@integration.test', 'correct-password');

        $client->request(
            'POST',
            '/api/auth',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'valid@integration.test', 'password' => 'wrong'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testPostAuthReturnsJwtForValidCredentials(): void
    {
        $client = static::createClient();
        $this->seedPatientWithProfile('jwt@integration.test', 'secret123');

        $token = $this->obtainJwt($client, 'jwt@integration.test', 'secret123');

        self::assertNotSame('', $token);
    }

    public function testGetMeWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetMeReturnsPatientProfileForPatient(): void
    {
        $client = static::createClient();
        $this->seedPatientWithProfile('me-patient@integration.test', 'p4ss-word');

        $token = $this->obtainJwt($client, 'me-patient@integration.test', 'p4ss-word');

        $client->request('GET', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('me-patient@integration.test', $data['email']);
        self::assertContains('ROLE_PATIENT', $data['roles']);
        self::assertArrayHasKey('patientProfile', $data);
        self::assertSame('Jan', $data['patientProfile']['firstName']);
        self::assertSame('Testowy', $data['patientProfile']['lastName']);
    }

    public function testGetMeReturnsEmptyPatientsForDoctorWithoutAssignments(): void
    {
        $client = static::createClient();
        $this->seedDoctor('doc@integration.test', 'doc-secret');

        $token = $this->obtainJwt($client, 'doc@integration.test', 'doc-secret');

        $client->request('GET', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('doc@integration.test', $data['email']);
        self::assertContains('ROLE_DOCTOR', $data['roles']);
        self::assertArrayHasKey('patients', $data);
        self::assertSame([], $data['patients']);
    }
}
