<?php

namespace App\Dto\Appointment\Response;

class AppointmentsClientResponseDto
{
    public function __construct(
        public int    $id,
        public string $appointmentType,
        public string $appointmentStatus,
        public string $petName,
        public string $date,
        public string $startTime,
        public ?string $veterinarian = null
    ){}
}