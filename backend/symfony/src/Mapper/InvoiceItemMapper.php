<?php

namespace App\Mapper;

use App\Dto\Invoice\Response\InvoiceItemResponseDto;
use App\Entity\InvoiceItem;

class InvoiceItemMapper
{
    /**
     * Convierte una entidad InvoiceItem a un DTO de respuesta.
     * @param InvoiceItem $item
     * @return InvoiceItemResponseDto
     */
    public function toResponseDto(InvoiceItem $item): InvoiceItemResponseDto
    {
        $service = $item->getService();

        return new InvoiceItemResponseDto(
            id: $item->getId(),
            serviceId: $service->getId(),
            serviceName: $service->getName(),
            quantity: $item->getQuantity(),
            unitPrice: $service->getUnitPrice(),
            taxRate: $service->getTaxRate(),
            subTotal: $item->getSubTotal(),
            taxAmount: $item->getTaxAmount(),
            totalAmount: $item->getTotalAmount(),
        );
    }

    /**
     * Convierte una colección de InvoiceItem a un array de DTOs de respuesta.
     *
     * @param iterable<InvoiceItem> $items
     * @return InvoiceItemResponseDto[]
     */
    public function toResponseDtoCollection(iterable $items): array
    {
        $dtos = [];
        foreach ($items as $item) {
            $dtos[] = $this->toResponseDto($item);
        }
        return $dtos;
    }
}