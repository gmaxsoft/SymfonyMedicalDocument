<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Me;
use App\Entity\PatientProfile;
use App\Entity\Prescription;
use App\Entity\User;
use App\Repository\PatientProfileRepository;
use App\Repository\PrescriptionRepository;
use DateTimeInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<Me>
 *
 * @psalm-suppress QueryBuilderSetParameter
 */
final class MeProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly PatientProfileRepository $patientProfileRepository,
        private readonly PrescriptionRepository $prescriptionRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Me
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $me = new Me();
        $me->email = (string) $user->getEmail();
        $me->roles = $user->getRoles();

        if (in_array('ROLE_PATIENT', $user->getRoles(), true)) {
            $profile = $this->patientProfileRepository->findOneBy(['user' => $user]);
            if ($profile instanceof PatientProfile) {
                $me->patientProfile = [
                    'id' => (int) $profile->getId(),
                    'firstName' => (string) $profile->getFirstName(),
                    'lastName' => (string) $profile->getLastName(),
                    'birthDate' => $profile->getBirthDate()?->format('Y-m-d'),
                ];
                $prescriptions = $this->prescriptionRepository->findBy(
                    ['patientProfile' => $profile],
                    ['issuedAt' => 'DESC'],
                );
                foreach ($prescriptions as $rx) {
                    $me->prescriptions[] = $this->serializePrescriptionSummary($rx);
                }
            }
        }

        if (in_array('ROLE_DOCTOR', $user->getRoles(), true)) {
            $profiles = $this->patientProfileRepository->createQueryBuilder('p')
                ->innerJoin('p.doctors', 'd')
                ->where('d = :doctor')
                ->setParameter('doctor', $user)
                ->orderBy('p.lastName', 'ASC')
                ->addOrderBy('p.firstName', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($profiles as $p) {
                if (!$p instanceof PatientProfile) {
                    continue;
                }
                $patientUser = $p->getUser();
                $me->patients[] = [
                    'id' => (int) $p->getId(),
                    'firstName' => (string) $p->getFirstName(),
                    'lastName' => (string) $p->getLastName(),
                    'patientEmail' => $patientUser ? (string) $patientUser->getEmail() : '',
                    'birthDate' => $p->getBirthDate()?->format('Y-m-d'),
                ];
            }
        }

        return $me;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePrescriptionSummary(Prescription $rx): array
    {
        $issuer = $rx->getIssuedBy();

        return [
            'verificationToken' => $rx->getVerificationToken(),
            'status' => $rx->getStatus()->value,
            'issuedAt' => $rx->getIssuedAt()?->format(DateTimeInterface::ATOM),
            'validUntil' => $rx->getValidUntil()?->format(DateTimeInterface::ATOM),
            'medications' => $rx->getMedications(),
            'instructions' => $rx->getInstructions(),
            'issuedByEmail' => $issuer ? (string) $issuer->getEmail() : null,
        ];
    }
}
