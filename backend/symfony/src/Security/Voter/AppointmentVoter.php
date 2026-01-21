<?php

namespace App\Security\Voter;

use App\Entity\Appointment;
use App\Entity\User;

class AppointmentVoter extends AbstractOwnerVoter
{
    /**
     * @inheritDoc
     */
    protected function getOwner(mixed $subject): ?User
    {
        return $subject instanceof Appointment
            ? $subject->getPet()?->getClient()
            : null;
    }

    /**
     * @inheritDoc
     */
    protected function getSupportedClass(): string
    {
        return Appointment::class;
    }

    protected function getCustomAttributes(): array
    {
        return [];
    }
}