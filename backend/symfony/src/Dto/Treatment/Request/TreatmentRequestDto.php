<?php

namespace App\Dto\Treatment\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TreatmentRequestDto
{
    #[Assert\NotBlank(message: 'El nombre del tratamiento es obligatorio.')]
    #[Assert\Length(
        max: 125,
        maxMessage: 'El nombre del tratamiento no puede exceder los {{ limit }} caracteres.'
    )]
    public string $name;

    #[Assert\NotBlank(message: 'El medicamento es obligatorio.')]
    #[Assert\Length(
        max: 125,
        maxMessage: 'El medicamento no puede exceder los {{ limit }} caracteres.'
    )]
    public string $medicine;

    #[Assert\NotBlank(message: 'La dosis es obligatoria.')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'La dosis no puede exceder los {{ limit }} caracteres.'
    )]
    public string $dose;

    #[Assert\NotBlank(message: 'La frecuencia es obligatoria.')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'La frecuencia no puede exceder los {{ limit }} caracteres.'
    )]
    public string $frequency;

    #[Assert\NotBlank(message: 'La fecha de inicio es obligatoria.')]
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Formato de fecha inválido. Usa YYYY-MM-DD (ej: 2025-12-27).'
    )]
    public string $startDate;

    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Formato de fecha inválido. Usa YYYY-MM-DD (ej: 2025-12-27).'
    )]
    public ?string $endDate = null;

    #[Assert\NotBlank(message: 'Las instrucciones son obligatorias.')]
    public string $instructions;

    #[Assert\Length(
        max: 250,
        maxMessage: 'El motivo de la suspensión no puede exceder los {{ limit }} caracteres.'
    )]
    public ?string $suspendedReason = null;
}