<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * PATCH payload: frontend sends full form (all fields). birthDate null clears stored date.
 */
final class PatientProfileUpdateInputDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $lastName = '';

    /**
     * ISO date Y-m-d or null to clear (validated in processor).
     */
    public ?string $birthDate = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';
}
