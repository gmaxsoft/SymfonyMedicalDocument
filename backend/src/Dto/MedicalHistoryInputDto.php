<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Input for creating a medical history entry.
 */
final class MedicalHistoryInputDto
{
    #[Assert\NotBlank(message: 'Patient profile IRI is required.')]
    public string $patientProfile = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title = '';

    #[Assert\NotBlank]
    public string $description = '';
}
