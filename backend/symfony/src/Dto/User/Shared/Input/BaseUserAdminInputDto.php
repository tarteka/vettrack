<?php

namespace App\Dto\User\Shared\Input;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class BaseUserAdminInputDto extends BaseUserFieldsInputDto
{
    #[Assert\Type('array')]
    #[Assert\Count(
        min: 1,
        minMessage: "Debe haber al menos un rol."
    )]
    #[Assert\All([
        new Assert\Choice(callback: [UserRole::class, 'getValues'], message: "Rol inválido"),
    ])]
    public ?array $roles = null;

    #[Assert\Length(
        max: 50,
        maxMessage: "El número de licencia no puede tener más de {{ limit }} caracteres."
    )]
    public ?string $licenseNumber = null;

    #[Assert\Length(
        max: 150,
        maxMessage: "La especialización no puede tener más de {{ limit }} caracteres."
    )]
    public ?string $specialization = null;
}