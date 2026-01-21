<?php

namespace App\Dto\Appointment\Response;

use DateTimeInterface;

readonly class AvailableDaySlotsResponseDto
{
    public function __construct(
        public int    $id,
        public string $startTime,
        public string $endTime
    ){}

}