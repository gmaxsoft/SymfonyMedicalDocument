<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PatientProfileUpdateInputDto;
use App\Entity\PatientProfile;
use App\Entity\User;
use App\Repository\PatientProfileRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<PatientProfileUpdateInputDto|PatientProfile, PatientProfile>
 */
final class PatientProfileUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PersistProcessor $persistProcessor,
        private readonly Security $security,
        private readonly PatientProfileRepository $patientProfileRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PatientProfile
    {
        if (!$data instanceof PatientProfileUpdateInputDto) {
            /** @var PatientProfile $persisted */
            $persisted = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            return $persisted;
        }

        $actor = $this->security->getUser();
        if (!$actor instanceof User || !$this->security->isGranted('ROLE_DOCTOR')) {
            throw new AccessDeniedException();
        }

        $id = $uriVariables['id'] ?? null;
        if (null === $id) {
            throw new UnprocessableEntityHttpException('Missing patient profile id.');
        }

        $profile = $this->patientProfileRepository->find($id);
        if (!$profile instanceof PatientProfile) {
            throw new NotFoundHttpException('Patient profile not found.');
        }

        if (!$profile->isTreatedBy($actor)) {
            throw new AccessDeniedException('You are not assigned to this patient.');
        }

        $patientUser = $profile->getUser();
        if (!$patientUser instanceof User) {
            throw new UnprocessableEntityHttpException('Patient profile has no user account.');
        }

        if ($patientUser->getEmail() !== $data->email) {
            $other = $this->userRepository->findOneBy(['email' => $data->email]);
            if (null !== $other && $other->getId() !== $patientUser->getId()) {
                throw new ConflictHttpException('An account with this email already exists.');
            }
            $patientUser->setEmail($data->email);
        }

        $profile->setFirstName($data->firstName);
        $profile->setLastName($data->lastName);
        $profile->setBirthDate($this->parseBirthDate($data->birthDate));

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
