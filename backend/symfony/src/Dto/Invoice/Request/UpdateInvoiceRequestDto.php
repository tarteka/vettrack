<?php

namespace App\Dto\Invoice\Request;

class UpdateInvoiceRequestDto
{
    public ?string $notes = null;

    /** @var InvoiceItemRequestDto[]|null */
    public ?array $invoiceItems = null;
}