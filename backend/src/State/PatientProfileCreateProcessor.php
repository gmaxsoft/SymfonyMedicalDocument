<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PatientProfileCreateInputDto;
use App\Entity\PatientProfile;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<PatientProfileCreateInputDto|PatientProfile, PatientProfile>
 */
final class PatientProfileCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PersistProcessor $persistProcessor,
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PatientProfile
    {
        if (!$data instanceof PatientProfileCreateInputDto) {
            /** @var PatientProfile $persisted */
            $persisted = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            return $persisted;
        }

        $actor = $this->security->getUser();
        if (!$actor instanceof User || !$this->security->isGranted('ROLE_DOCTOR')) {
            throw new AccessDeniedException();
        }

        if (null !== $this->userRepository->findOneBy(['email' => $data->email])) {
            throw new ConflictHttpException('An account with this email already exists.');
        }

        $birthDate = $this->parseBirthDate($data->birthDate);

        $patientUser = (new User())
            ->setEmail($data->email)
            ->setRoles(['ROLE_PATIENT']);
        $patientUser->setPassword($this->passwordHasher->hashPassword($patientUser, $data->plainPassword));

        $profile = (new PatientProfile())
            ->setUser($patientUser)
            ->setFirstName($data->firstName)
            ->setLastName($data->lastName)
            ->setBirthDate($birthDate);
        $profile->addDoctor($actor);
        $patientUser->setPatientProfile($profile);

        /** @var PatientProfile $persisted */
        $persisted = $this->persistProcessor->process($profile, $operation, $uriVariables, $context);

        return $persisted;
    }

    private function parseBirthDate(?string $value): ?DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw new UnprocessableEntityHttpException('Invalid birthDate; use Y-m-d.');
        }
    }
}
