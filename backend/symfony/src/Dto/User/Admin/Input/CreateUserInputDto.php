<?php

namespace App\Dto\User\Admin\Input;

use App\Dto\User\Shared\Input\BaseUserAdminInputDto;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserInputDto extends BaseUserAdminInputDto
{
    #[Assert\NotBlank]
    public ?string $email;

    #[Assert\NotBlank]
    public ?string $dni;

    #[Assert\NotBlank]
    public ?string $firstName;

    #[Assert\NotBlank]
    public ?string $lastName;

    #[Assert\NotBlank]
    public ?array $roles;
}