<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\PatientProfileCreateInputDto;
use App\Dto\PatientProfileUpdateInputDto;
use App\Repository\PatientProfileRepository;
use App\State\PatientProfileCreateProcessor;
use App\State\PatientProfileDeleteProcessor;
use App\State\PatientProfileUpdateProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: PatientProfileRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['patient_profile:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
        ),
        new Get(
            normalizationContext: ['groups' => ['patient_profile:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
            security: "is_granted('ROLE_DOCTOR') and object.isTreatedBy(user) or (is_granted('ROLE_PATIENT') and object.getUser() and object.getUser().getId() == user.getId())",
        ),
        new Post(
            input: PatientProfileCreateInputDto::class,
            processor: PatientProfileCreateProcessor::class,
            security: "is_granted('ROLE_DOCTOR')",
            normalizationContext: ['groups' => ['patient_profile:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
        ),
        new Patch(
            input: PatientProfileUpdateInputDto::class,
            processor: PatientProfileUpdateProcessor::class,
            security: "is_granted('ROLE_DOCTOR') and object.isTreatedBy(user)",
            normalizationContext: ['groups' => ['patient_profile:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
        ),
        new Delete(
            security: "is_granted('ROLE_DOCTOR') and object.isTreatedBy(user)",
            processor: PatientProfileDeleteProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['patient_profile:read'], DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
)]
class PatientProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['patient_profile:read', 'prescription:read', 'history:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'patientProfile', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ApiProperty(readableLink: false)]
    #[Groups(['patient_profile:read'])]
    private ?User $user = null;

    /**
     * Doctors (clinic staff) who may access this patient's record.
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'patient_profile_doctor', joinColumns: [new ORM\JoinColumn(name: 'patient_profile_id', referencedColumnName: 'id', onDelete: 'CASCADE')], inverseJoinColumns: [new ORM\JoinColumn(name: 'doctor_id', referencedColumnName: 'id', onDelete: 'CASCADE')])]
    #[Groups(['patient_profile:read'])]
    private Collection $doctors;

    #[ORM\Column(length: 120)]
    #[Groups(['patient_profile:read', 'prescription:read', 'history:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 120)]
    #[Groups(['patient_profile:read', 'prescription:read', 'history:read'])]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['patient_profile:read', 'prescription:read', 'history:read'])]
    private ?DateTimeImmutable $birthDate = null;

    public function __construct()
    {
        $this->doctors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getDoctors(): Collection
    {
        return $this->doctors;
    }

    public function addDoctor(User $doctor): static
    {
        if (!$this->isTreatedBy($doctor)) {
            $this->doctors->add($doctor);
        }

        return $this;
    }

    public function removeDoctor(User $doctor): static
    {
        $this->doctors->removeElement($doctor);

        return $this;
    }

    public function isTreatedBy(User $user): bool
    {
        $uid = $user->getId();
        if (null === $uid) {
            return false;
        }

        foreach ($this->doctors as $d) {
            if ($d->getId() === $uid) {
                return true;
            }
        }

        return false;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthDate(): ?DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(?DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }
}
