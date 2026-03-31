<?php

declare(strict_types=1);

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\MedicalHistory;
use App\Entity\PatientProfile;
use App\Entity\Prescription;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Restricts API collections: doctors see only patients they share (clinic roster); patients see only their own data.
 *
 * @psalm-suppress QueryBuilderSetParameter
 */
final class DoctorPatientAccessExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!in_array($resourceClass, [PatientProfile::class, Prescription::class, MedicalHistory::class], true)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->denyAll($queryBuilder);

            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        match ($resourceClass) {
            PatientProfile::class => $this->filterPatientProfiles($queryBuilder, $rootAlias, $user),
            Prescription::class => $this->filterPrescriptions($queryBuilder, $rootAlias, $user),
            MedicalHistory::class => $this->filterMedicalHistory($queryBuilder, $rootAlias, $user),
            default => null,
        };
    }

    private function denyAll(QueryBuilder $qb): void
    {
        $qb->andWhere('1 = 0');
    }

    private function filterPatientProfiles(QueryBuilder $qb, string $alias, User $user): void
    {
        if ($this->security->isGranted('ROLE_PATIENT')) {
            $qb->andWhere(sprintf('%s.user = :_dpa_user', $alias))
                ->setParameter('_dpa_user', $user);

            return;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            $qb->innerJoin(sprintf('%s.doctors', $alias), '_dpa_doc')
                ->andWhere('_dpa_doc = :_dpa_doctor')
                ->setParameter('_dpa_doctor', $user);

            return;
        }

        $this->denyAll($qb);
    }

    private function filterPrescriptions(QueryBuilder $qb, string $alias, User $user): void
    {
        $qb->innerJoin(sprintf('%s.patientProfile', $alias), '_dpa_pp');

        if ($this->security->isGranted('ROLE_PATIENT')) {
            $qb->andWhere('_dpa_pp.user = :_dpa_user')
                ->setParameter('_dpa_user', $user);

            return;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            $qb->innerJoin('_dpa_pp.doctors', '_dpa_doc')
                ->andWhere('_dpa_doc = :_dpa_doctor')
                ->setParameter('_dpa_doctor', $user);

            return;
        }

        $this->denyAll($qb);
    }

    private function filterMedicalHistory(QueryBuilder $qb, string $alias, User $user): void
    {
        $qb->innerJoin(sprintf('%s.patientProfile', $alias), '_dpa_pp');

        if ($this->security->isGranted('ROLE_PATIENT')) {
            $qb->andWhere('_dpa_pp.user = :_dpa_user')
                ->setParameter('_dpa_user', $user);

            return;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            $qb->innerJoin('_dpa_pp.doctors', '_dpa_doc')
                ->andWhere('_dpa_doc = :_dpa_doctor')
                ->setParameter('_dpa_doctor', $user);

            return;
        }

        $this->denyAll($qb);
    }
}
