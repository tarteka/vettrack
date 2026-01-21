<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Clase abstracta que centraliza la lógica común para comprobar el "owner" de un subject.
 * - Implementar getSupportedClass() y getOwner() en los hijos.
 */
abstract class AbstractOwnerVoter extends Voter
{
    public const VIEW = 'VIEW';

    /**
     * Obtiene el propietario del recurso.
     * Retorna el User dueño del animal, factura, cita, tratamiento o vacuna.
     */
    abstract protected function getOwner(mixed $subject): ?User;

    /**
     * Retorna la clase que este voter soporta (Pet::class, Invoice::class, etc.)
     */
    abstract protected function getSupportedClass(): string;

    /**
     * Retorna los atributos personalizados que este voter soporta además de VIEW.
     */
    abstract protected function getCustomAttributes(): array;


    protected function getSupportedAttributes(): array
    {
        $defaultAttributes = [
            self::VIEW
        ];

        $supportedAttributes = array_merge($defaultAttributes, $this->getCustomAttributes());

        return array_unique($supportedAttributes);
    }

    /**
     * Retorna los atributos que el usuario autenticado puede ver.
     * De forma predeterminada, solo puede ver VIEW.
     */
    protected function getOwnerAllowedAttributes(): array
    {
        return [self::VIEW];
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, $this->getSupportedAttributes())) {
            return false;
        }

        $supportedClass = $this->getSupportedClass();
        return $subject instanceof $supportedClass;
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

        // Admin y Vet pueden ver todo
        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true) ||
            in_array('ROLE_VET', $currentUser->getRoles(), true)) {
            return true;
        }

        // Cliente: verificar que es el propietario
        $owner = $this->getOwner($subject);

        if (!$owner || $owner->getId() !== $currentUser->getId()) {
            return false;
        }

        return in_array($attribute, $this->getOwnerAllowedAttributes(), true);
    }
}