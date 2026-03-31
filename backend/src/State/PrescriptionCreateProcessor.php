<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PrescriptionInputDto;
use App\Entity\PatientProfile;
use App\Entity\Prescription;
use App\Entity\User;
use App\Enum\PrescriptionStatus;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<PrescriptionInputDto|Prescription, Prescription>
 */
final class PrescriptionCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PersistProcessor $persistProcessor,
        private readonly Security $security,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Prescription
    {
        if (!$data instanceof PrescriptionInputDto) {
            /** @var Prescription $persisted */
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

        $now = new DateTimeImmutable();
        $validUntil = $data->validUntil
            ? DateTimeImmutable::createFromInterface($data->validUntil)
            : $now->modify('+90 days');

        $medications = [];
        foreach ($data->medications as $line) {
            $medications[] = [
                'name' => (string) $line['name'],
                'dosage' => (string) $line['dosage'],
                'instructions' => (string) ($line['instructions'] ?? ''),
            ];
        }

        $prescription = (new Prescription())
            ->setPatientProfile($patient)
            ->setIssuedBy($user)
            ->setMedications($medications)
            ->setInstructions($data->instructions)
            ->setStatus(PrescriptionStatus::ACTIVE)
            ->setIssuedAt($now)
            ->setValidUntil($validUntil)
            ->setVerificationToken(Prescription::generateVerificationToken());

        /** @var Prescription $persisted */
        $persisted = $this->persistProcessor->process($prescription, $operation, $uriVariables, $context);

        return $persisted;
    }
}
