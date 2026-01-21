<?php

namespace App\Security\Voter;

use App\Entity\Treatment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TreatmentVoter extends AbstractOwnerVoter
{
    public const VIEW = 'VIEW';
    public const UPDATE = 'UPDATE';
    public const DELETE = 'DELETE';

    /**
     * @inheritDoc
     */
    protected function getOwner(mixed $subject): ?User
    {
        return $subject instanceof Treatment
            ? $subject->getPet()?->getClient()
            : null;
    }

    /**
     * @inheritDoc
     */
    protected function getSupportedClass(): string
    {
        return Treatment::class;
    }

    protected function getCustomAttributes(): array
    {
        return ['UPDATE', 'DELETE'];
    }

    protected function getOwnerAllowedAttributes(): array
    {
        return [self::VIEW];
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();
        if (!$currentUser instanceof User) {
            return false;
        }

        // Admin puede TODO
        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        // VET: puede VIEW todo, pero UPDATE/DELETE solo suyos
        if (in_array('ROLE_VET', $currentUser->getRoles(), true)) {
            if ($attribute === self::VIEW) {
                return true; // VET ve todo
            }

            // UPDATE/DELETE: solo si es el veterinario del tratamiento
            return $subject instanceof Treatment &&
                $subject->getVeterinarian()->getId() === $currentUser->getId();
        }
        // Cliente: solo VIEW si es dueño de la mascota
        $owner = $this->getOwner($subject);
        if (!$owner || $owner->getId() !== $currentUser->getId()) {
            return false;
        }

        return in_array($attribute, $this->getOwnerAllowedAttributes(), true);

    }
}