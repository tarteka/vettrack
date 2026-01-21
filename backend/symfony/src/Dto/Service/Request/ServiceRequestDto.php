<?php

namespace App\Dto\Service\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ServiceRequestDto
{
    #[Assert\NotBlank(message: 'El nombre del servicio es obligatorio.')]
    #[Assert\Length(
        max: 200,
        maxMessage: 'El nombre no puede exceder {{ limit }} caracteres.'
    )]
    public string $name;
    public ?string $description = null;
    #[Assert\NotNull(message: 'El precio unitario es obligatorio.')]
    #[Assert\PositiveOrZero(message: 'El precio unitario debe ser un número positivo.')]
    public float $unitPrice;

    #[Assert\NotNull(message: 'La tasa de impuesto es obligatoria.')]
    #[Assert\Range(
        notInRangeMessage: 'La tasa de impuesto debe estar entre 0 y 100.',
        min: 0,
        max: 100
    )]
    public float $taxRate;

    #[Assert\NotNull(message: 'El estado de actividad es obligatorio.')]
    #[Assert\Type(type: 'bool', message: 'El estado de actividad debe ser booleano.')]
    public bool $isActive = true;

    #[Assert\NotNull(message: 'La categoría de servicio es obligatoria.')]
    #[Assert\Positive(message: 'El ID de categoría debe ser un número positivo.')]
    public int $categoryId;
}