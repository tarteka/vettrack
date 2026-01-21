<?php

namespace App\Mapper;

use App\Dto\Invoice\Response\InvoiceResponseDto;
use App\Entity\Invoice;
use App\Repository\InvoiceSequenceRepository;

class InvoiceMapper
{

    public function __construct(
        private readonly InvoiceItemMapper $invoiceItemMapper,
    ){}

    /**
     * Convierte una entidad Invoice en un DTO de respuesta.
     */
    public function toResponseDto(Invoice $invoice): InvoiceResponseDto
    {
        return new InvoiceResponseDto(
            id: $invoice->getId(),
            invoiceNumber: $invoice->getInvoiceNumber(),
            invoiceDate: $invoice->getInvoiceDate()->format('d-m-Y'),
            clientName: $invoice->getUser()->getFullName(),
            petName: $invoice->getPet()->getName(),
            subtotal: $invoice->getSubtotal(),
            taxAmount: $invoice->getTaxAmount(),
            totalAmount: $invoice->getTotalAmount(),
            status: $invoice->getStatus()->value,
            invoiceItems: $this->invoiceItemMapper->toResponseDtoCollection($invoice->getInvoiceItems()),
            notes: $invoice->getNotes()
        );
    }

    /**
     * Convierte una colección de Invoice a DTOs.
     *
     * @param iterable<Invoice> $invoices
     * @return InvoiceResponseDto[]
     */
    public function toResponseDtoCollection(iterable $invoices): array
    {
        $dtos = [];
        foreach ($invoices as $invoice) {
            $dtos[] = $this->toResponseDto($invoice);
        }
        return $dtos;
    }
}