<?php

namespace App\Dto\Appointment\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CalendarEventRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'La fecha de inicio es obligatoria.')]
        #[Assert\Date(message: 'La fecha de inicio debe tener formato YYYY-MM-DD')]
        public string $startDate,

        #[Assert\NotBlank(message: 'La fecha de fin es obligatoria.')]
        #[Assert\Date(message: 'La fecha de fin debe tener formato YYYY-MM-DD')]
        #[Assert\GreaterThanOrEqual(
            propertyPath: 'startDate',
            message: 'La fecha de fin debe ser posterior o igual a la fecha de inicio.'
        )]
        public string $endDate
    ) {}
}