<?php

declare(strict_types=1);

namespace App\Api;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\MeProvider;

/**
 * Current account snapshot: profile, prescriptions (patients), or assigned patients (doctors).
 * Exposed at GET /api/me — no numeric resource IDs in the URL.
 */
#[ApiResource(
    shortName: 'Me',
    operations: [
        new Get(
            uriTemplate: '/me',
            security: "is_granted('ROLE_USER')",
            provider: MeProvider::class,
        ),
    ],
)]
class Me
{
    public string $email = '';

    /** @var list<string> */
    public array $roles = [];

    /** @var array{id: int, firstName: string, lastName: string, birthDate: string|null}|null */
    public ?array $patientProfile = null;

    /**
     * Patient's prescriptions (summary; use verificationToken as stable client key).
     *
     * @var list<array<string, mixed>>
     */
    public array $prescriptions = [];

    /**
     * Doctor's assigned patients (clinic roster).
     *
     * @var list<array{id: int, firstName: string, lastName: string, patientEmail: string, birthDate: string|null}>
     */
    public array $patients = [];
}
