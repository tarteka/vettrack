<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends AbstractOwnerVoter
{
    public const EDIT = 'EDIT';
    public const EDIT_ROLES = 'EDIT_ROLES';

    protected function getCustomAttributes(): array
    {
        return [
            self::EDIT,
            self::EDIT_ROLES,
        ];
    }

    protected function getOwner(mixed $subject): ?User
    {
        return $subject instanceof User ? $subject : null;
    }

    protected function getSupportedClass(): string
    {
        return User::class;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        // Usuario no autenticado = sin acceso
        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $user */
        $user = $subject;

        // Admin puede hacer cualquier cosa
        if (in_array(UserRole::Admin->value, $currentUser->getRoles(), true)) {
            return true;
        }

        return match($attribute) {
            self::VIEW => $this->canView($user, $currentUser),
            self::EDIT => $this->canEdit($user, $currentUser),
            self::EDIT_ROLES => false, // Solo admin puede editar roles, ya cubierto arriba
            default => false
        };
    }

    private function canView(User $entity, User $currentUser): bool
    {
        // Dar acceso a los mismos usuarios que pueden editar
        return $this->canEdit($entity, $currentUser);
    }

    private function canEdit(User $entity, User $currentUser): bool
    {
        // Permitir que el usuario edite su propio perfil
        if ($currentUser->getId() === $entity->getId()) {
            return true;
        }

        // Si el usuario autenticado es veterinario
        if (in_array(UserRole::Vet->value, $currentUser->getRoles(), true)) {
            // Solo puede editar usuarios que sean exclusivamente cliente
            // No puede editar veterinarios, admins ni usuarios con más de un rol
            return $entity->getRoles() === [UserRole::Client->value];
        }

        return false;
    }
}