<?php

namespace App\Services\Invoice;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Service;
use App\Enum\InvoiceStatus;
use App\Repository\ServiceRepository;
use App\Dto\Invoice\Request\UpdateInvoiceRequestDto;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class InvoiceUpdaterService{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceRepository $serviceRepository
    ){}

    /**
     * Actualiza los datos de una factura.
     * Los datos modificados incluyen:
     * - Notas
     * - Items de la factura
     *
     * @param Invoice $invoice
     * @param UpdateInvoiceRequestDto $dto
     * @return Invoice
     */
    public function update(
        Invoice $invoice,
        UpdateInvoiceRequestDto $dto
    ): Invoice
    {
        // No se puede modificar una factura pagada o cancelada.
        if (in_array($invoice->getStatus(), [InvoiceStatus::Paid, InvoiceStatus::Cancelled], true)) {
            throw new DomainException('No se puede modificar una factura pagada o cancelada.');
        }

        if ($dto->notes !== null) {
            $invoice->setNotes($dto->notes);
        }

        if ($dto->invoiceItems !== null) {
            // Eliminar los items de la factura actual.
            foreach ($invoice->getInvoiceItems() as $item) {
                $invoice->removeInvoiceItem($item);
            }

            // Agregar los nuevos items.
            foreach ($dto->invoiceItems as $itemDto) {
                $service = $this->serviceRepository->find($itemDto->serviceId);

                if (!$service instanceof Service) {
                    throw new \DomainException('Servicio no encontrado');
                }
                if (!$service->isActive()) {
                    throw new \DomainException('El servicio no está activo');
                }

                $item = new InvoiceItem();
                $item->setService($service);
                $item->setQuantity($itemDto->quantity); // Recalcula los precios internamente

                $invoice->addInvoiceItem($item);
            }
            if ($invoice->getInvoiceItems()->isEmpty()) {
                throw new \DomainException('La factura debe tener al menos una línea de servicio.');
            }

            $invoice->updateTotals();
        }
        $this->entityManager->flush();
        return $invoice;
    }
}