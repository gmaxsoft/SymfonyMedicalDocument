<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\MedicalHistoryInputDto;
use App\Entity\MedicalHistory;
use App\Entity\PatientProfile;
use App\Entity\User;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<MedicalHistoryInputDto|MedicalHistory, MedicalHistory>
 */
final class MedicalHistoryCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PersistProcessor $persistProcessor,
        private readonly Security $security,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MedicalHistory
    {
        if (!$data instanceof MedicalHistoryInputDto) {
            /** @var MedicalHistory $persisted */
            $persisted = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

            return $persisted;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        try {
            $patient = $this->iriConverter->getResourceFromIri($data->patientProfile);
        } catch (Exception) {
            throw new BadRequestHttpException('Invalid patient profile IRI.');
        }

        if (!$patient instanceof PatientProfile) {
            throw new BadRequestHttpException('Resource is not a patient profile.');
        }

        if (!$patient->isTreatedBy($user)) {
            throw new AccessDeniedException('You are not linked to this patient as a treating doctor.');
        }

        $entry = (new MedicalHistory())
            ->setPatientProfile($patient)
            ->setRecordedBy($user)
            ->setTitle($data->title)
            ->setDescription($data->description)
            ->setRecordedAt(new DateTimeImmutable());

        /** @var MedicalHistory $persisted */
        $persisted = $this->persistProcessor->process($entry, $operation, $uriVariables, $context);

        return $persisted;
    }
}
