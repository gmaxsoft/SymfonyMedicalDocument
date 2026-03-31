<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PatientProfileCreateInputDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 4096)]
    public string $plainPassword = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $lastName = '';

    /**
     * ISO date Y-m-d (optional; validated in processor).
     */
    public ?string $birthDate = null;
}
