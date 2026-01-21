<?php

namespace App\Dto\Appointment\Response;

class CalendarEventResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $start,
        public readonly string $end,
        public readonly array $extendedProps = []
    ){}
}