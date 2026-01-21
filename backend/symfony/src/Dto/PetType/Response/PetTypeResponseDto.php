<?php

namespace App\Dto\PetType\Response;

final class PetTypeResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description
    ) {}

}