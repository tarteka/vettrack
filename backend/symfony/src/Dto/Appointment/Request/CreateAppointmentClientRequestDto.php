<?php

namespace App\Dto\Appointment\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateAppointmentClientRequestDto
{
    #[Assert\NotBlank(message: 'La mascota es obligatoria')]
    #[Assert\Positive(message: 'El identificador de la mascota no es válido')]
    public int $petId;

    #[Assert\NotBlank(message: 'El tipo de consulta es obligatorio')]
    #[Assert\Positive(message: 'El identificador del tipo de consulta no es válido')]
    public int $appointmentTypeId;

    #[Assert\NotBlank(message: 'El slot de cita es obligatorio')]
    #[Assert\Positive(message: 'El identificador del slot no es válido')]
    public int $appointmentSlotId;

    #[Assert\NotBlank(message: 'El motivo es obligatorio')]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Las notas no pueden superar {{ limit }} caracteres'
    )]
    public string $reason;
}