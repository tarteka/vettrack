<?php

namespace App\Dto\Invoice\Response;

class InvoiceResponseDto
{
    /**
     * @param InvoiceItemResponseDto[] $invoiceItems
     */
    public function __construct(
        public int $id,
        public string $invoiceNumber,
        public string $invoiceDate,
        public string $clientName,
        public string $petName,
        public float $subtotal,
        public float $taxAmount,
        public float $totalAmount,
        public string $status,
        public array $invoiceItems,
        public ?string $notes
    ) {}
}