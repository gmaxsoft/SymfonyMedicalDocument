<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\MedicalHistoryInputDto;
use App\Repository\MedicalHistoryRepository;
use App\State\MedicalHistoryCreateProcessor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MedicalHistoryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['history:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['history:read']],
            security: "is_granted('MEDICAL_HISTORY_VIEW', object)",
        ),
        new Post(
            input: MedicalHistoryInputDto::class,
            processor: MedicalHistoryCreateProcessor::class,
            security: "is_granted('MEDICAL_HISTORY_CREATE')",
            normalizationContext: ['groups' => ['history:read']],
        ),
    ],
)]
class MedicalHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['history:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['history:read'])]
    private ?PatientProfile $patientProfile = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['history:read'])]
    private ?User $recordedBy = null;

    #[ORM\Column(length: 255)]
    #[Groups(['history:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['history:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['history:read'])]
    private ?DateTimeImmutable $recordedAt = null;

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

    public function getRecordedBy(): ?User
    {
        return $this->recordedBy;
    }

    public function setRecordedBy(?User $recordedBy): static
    {
        $this->recordedBy = $recordedBy;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRecordedAt(): ?DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(DateTimeImmutable $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }
}
