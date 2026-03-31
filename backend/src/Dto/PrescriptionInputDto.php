<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Input payload for issuing a prescription (API Platform Post input).
 */
final class PrescriptionInputDto
{
    #[Assert\NotBlank(message: 'Patient profile IRI is required.')]
    public string $patientProfile = '';

    /**
     * @var list<array{name?: string, dosage?: string, instructions?: string}>
     */
    #[Assert\NotBlank(message: 'At least one medication line is required.')]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'name' => [new Assert\NotBlank()],
                'dosage' => [new Assert\NotBlank()],
                'instructions' => new Assert\Optional([new Assert\Type('string')]),
            ],
            allowExtraFields: true,
            allowMissingFields: false,
        ),
    ])]
    public array $medications = [];

    #[Assert\NotBlank(message: 'Global instructions are required.')]
    public string $instructions = '';

    #[Assert\Type(DateTimeInterface::class)]
    public ?DateTimeInterface $validUntil = null;
}
