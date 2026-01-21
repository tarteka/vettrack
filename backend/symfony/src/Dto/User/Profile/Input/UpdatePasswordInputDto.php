<?php

namespace App\Dto\User\Profile\Input;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePasswordInputDto
{
    #[Assert\NotBlank(message: 'La contraseña actual es obligatoria.')]
    public string $currentPassword;

    #[Assert\NotBlank(message: 'La nueva contraseña es obligatoria.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'La nueva contraseña debe tener al menos {{ limit }} caracteres.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'La contraseña debe contener al menos una mayúscula, una minúscula y un número'
    )]
    public string $newPassword;

    #[Assert\NotBlank(message: 'La confirmación de la nueva contraseña es obligatoria.')]
    #[Assert\EqualTo(
        propertyPath: 'newPassword',
        message: 'Las contraseñas no coinciden.'
    )]
    public string $confirmPassword;
}