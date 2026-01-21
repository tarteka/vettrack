<?php

namespace App\Dto\Treatment\Response;

class TreatmentResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $medicine,
        public string $dose,
        public string $frequency,
        public string $instructions,
        public string $startDate,
        public ?string $endDate = null,
        public string $veterinarianFullName,
        public string $duration,
        public string $status,
        public string $suspendedReason
    ) {}
}