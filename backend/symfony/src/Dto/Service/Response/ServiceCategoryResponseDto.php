<?php

namespace App\Dto\Service\Response;

class ServiceCategoryResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description
    ) {}
}