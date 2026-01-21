<?php

namespace App\Dto\Service\Response;


class ServiceResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public float $price,
        public float $taxRate,
        public bool $isActive,
        public ServiceCategoryResponseDto $category
    ) {}
}