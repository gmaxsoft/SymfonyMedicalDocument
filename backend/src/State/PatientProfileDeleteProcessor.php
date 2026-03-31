<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PatientProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Removes patient profile and linked user account (prescriptions / history cascade via FK).
 *
 * @implements ProcessorInterface<PatientProfile, null>
 */
final class PatientProfileDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        if (!$data instanceof PatientProfile) {
            return null;
        }

        $user = $data->getUser();
        if (!$user instanceof User) {
            $this->entityManager->remove($data);
            $this->entityManager->flush();

            return null;
        }

        if (!in_array('ROLE_PATIENT', $user->getRoles(), true)) {
            throw new AccessDeniedException('This profile is not a patient account.');
        }

        $this->entityManager->remove($data);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return null;
    }
}
