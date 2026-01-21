<?php

namespace App\Security\Voter;

use App\Entity\Invoice;
use App\Entity\User;

class InvoiceVoter extends AbstractOwnerVoter
{
    /**
     * @inheritDoc
     */
    protected function getOwner(mixed $subject): ?User
    {
        return $subject instanceof Invoice ? $subject->getUser() : null;
    }

    /**
     * @inheritDoc
     */
    protected function getSupportedClass(): string
    {
        return Invoice::class;
    }

    protected function getCustomAttributes(): array
    {
        return [];
    }
}