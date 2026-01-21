<?php

namespace App\Dto\MedicalRecord\Request;

use App\Enum\MedicalRecordType;
use Symfony\Component\Validator\Constraints as Assert;

class MedicalRecordRequestDto
{
    #[Assert\NotBlank(message: 'El tipo de consulta es obligatorio.')]
    #[Assert\Choice(
        choices: MedicalRecordType::CHOICES,
        message: 'Tipo inválido. Valores permitidos: {{ choices }}'
    )]
    public string $type;

    #[Assert\NotBlank(message: 'El diagnóstico es obligatorio.')]
    public string $diagnosis;

    public ?string $procedures = null;

    #[Assert\NotBlank(message: 'La descripción es obligatoria.')]
    public string $description;

    public ?string $notes = null;

}