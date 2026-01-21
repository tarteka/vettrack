<?php

namespace App\Dto\Pet;

use App\Dto\PetType\Response\PetTypeResponseDto;

final class PetResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isActive,
        public PetTypeResponseDto $petType,
        public ?string $breed,
        public ?string $birthDate,
        public ?string $age,
        public string $gender,
        public ?string $color,
        public ?string $microchip,
        public ?float $weight,
        public ?string $allergies,
        public ?bool $sterilized,
        public ?string $insuranceProvider,
        public ?string $insurancePolicyNumber,
        public ?string $lastAppointmentDate,
        public ?string $notes,
        public array $client,
    ) {}
}