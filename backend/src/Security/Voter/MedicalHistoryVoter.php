<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\MedicalHistory;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class MedicalHistoryVoter extends Voter
{
    public const VIEW = 'MEDICAL_HISTORY_VIEW';

    public const CREATE = 'MEDICAL_HISTORY_CREATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::CREATE === $attribute) {
            return null === $subject;
        }

        return self::VIEW === $attribute && $subject instanceof MedicalHistory;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (self::CREATE === $attribute) {
            return in_array('ROLE_DOCTOR', $user->getRoles(), true);
        }

        if (!$subject instanceof MedicalHistory) {
            return false;
        }

        return $this->canView($subject, $user);
    }

    private function canView(MedicalHistory $history, User $user): bool
    {
        $profile = $history->getPatientProfile();
        if (null === $profile) {
            return false;
        }

        if (in_array('ROLE_PATIENT', $user->getRoles(), true) && $profile->getUser()?->getId() === $user->getId()) {
            return true;
        }

        if (in_array('ROLE_DOCTOR', $user->getRoles(), true) && $profile->isTreatedBy($user)) {
            return true;
        }

        return false;
    }
}
