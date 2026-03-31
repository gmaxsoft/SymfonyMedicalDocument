<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\PrescriptionInputDto;
use App\Enum\PrescriptionStatus;
use App\Repository\PrescriptionRepository;
use App\State\PrescriptionCreateProcessor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['prescription:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['prescription:read']],
            security: "is_granted('PRESCRIPTION_VIEW', object)",
        ),
        new Post(
            input: PrescriptionInputDto::class,
            processor: PrescriptionCreateProcessor::class,
            security: "is_granted('PRESCRIPTION_CREATE')",
            normalizationContext: ['groups' => ['prescription:read']],
        ),
    ],
)]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['prescription:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['prescription:read'])]
    private ?PatientProfile $patientProfile = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['prescription:read'])]
    private ?User $issuedBy = null;

    /**
     * @var list<array{name: string, dosage: string, instructions?: string}>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['prescription:read'])]
    private array $medications = [];

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['prescription:read'])]
    private ?string $instructions = null;

    #[ORM\Column(enumType: PrescriptionStatus::class)]
    #[Groups(['prescription:read'])]
    private PrescriptionStatus $status = PrescriptionStatus::ACTIVE;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['prescription:read'])]
    private ?DateTimeImmutable $issuedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['prescription:read'])]
    private ?DateTimeImmutable $validUntil = null;

    #[ORM\Column(length: 36, unique: true)]
    #[Groups(['prescription:read'])]
    private ?string $verificationToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientProfile(): ?PatientProfile
    {
        return $this->patientProfile;
    }

    public function setPatientProfile(?PatientProfile $patientProfile): static
    {
        $this->patientProfile = $patientProfile;

        return $this;
    }

    public function getIssuedBy(): ?User
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(?User $issuedBy): static
    {
        $this->issuedBy = $issuedBy;

        return $this;
    }

    /**
     * @return list<array{name: string, dosage: string, instructions?: string}>
     */
    public function getMedications(): array
    {
        return $this->medications;
    }

    /**
     * @param list<array{name: string, dosage: string, instructions?: string}> $medications
     */
    public function setMedications(array $medications): static
    {
        $this->medications = $medications;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getStatus(): PrescriptionStatus
    {
        return $this->status;
    }

    public function setStatus(PrescriptionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getIssuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public static function generateVerificationToken(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
