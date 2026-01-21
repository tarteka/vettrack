<?php

namespace App\Security\Voter;

use App\Entity\Pet;
use App\Entity\User;

class PetVoter extends AbstractOwnerVoter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const CREATE = 'CREATE';
    public const UPDATE = 'UPDATE';

    /**
     * @inheritDoc
     */
    protected function getOwner(mixed $subject): ?User
    {
        return $subject instanceof Pet ? $subject->getClient() : null;
    }

    /**
     * @inheritDoc
     */
    protected function getSupportedClass(): string
    {
        return Pet::class;
    }

    protected function getCustomAttributes(): array
    {
        return [
            self::EDIT,
            self::DELETE,
            self::CREATE,
            self::UPDATE
        ];
    }
}