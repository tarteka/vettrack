<?php

namespace App\Dto\Service\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ServiceCategoryRequestDto
{
    #[Assert\NotBlank(message: 'El nombre de la categoría es obligatorio.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'El nombre no puede exceder {{ limit }} caracteres.'
    )]
    public string $name;
    public ?string $description = null;
}