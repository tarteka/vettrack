<?php

namespace App\Dto\MedicalRecord\Response;

class MedicalRecordResponseDto
{
    public function __construct(
        public int $id,
        public int $petId,
        public string $type,
        public string $description,
        public string $veterinarian,
        public ?string $diagnosis,
        public ?string $notes,
        public ?string $procedures,
        public string $date,
    ) {}
}