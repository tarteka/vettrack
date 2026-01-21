<?php

namespace App\Dto\PetType\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class PetTypeRequestDto
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'El nombre no puede superar {{ limit }} caracteres'
    )]
    public string $name;

    public string $description;
}