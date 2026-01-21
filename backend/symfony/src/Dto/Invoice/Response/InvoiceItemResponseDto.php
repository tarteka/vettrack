<?php

namespace App\Dto\Invoice\Response;

class InvoiceItemResponseDto
{
    public function __construct(
        public int $id,
        public int $serviceId,
        public string $serviceName,
        public int $quantity,
        public float $unitPrice,
        public float $taxRate,
        public float $subTotal,
        public float $taxAmount,
        public float $totalAmount,
    ) {}
}