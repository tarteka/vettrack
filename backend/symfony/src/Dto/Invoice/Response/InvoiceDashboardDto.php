<?php

namespace App\Dto\Invoice\Response;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use DateTimeInterface;

class InvoiceDashboardDto
{
    private int $id;
    private float $totalAmount;
    private DateTimeInterface $invoiceDate;
    private string $invoiceNumber;
    private InvoiceStatus $status;

    /**
     * Constructor
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->id = $invoice->getId();
        $this->totalAmount = $invoice->getTotalAmount();
        $this->invoiceDate = $invoice->getInvoiceDate();
        $this->invoiceNumber = $invoice->getInvoiceNumber();
        $this->status = $invoice->getStatus();
    }

    /**
     * Convierte el DTO a un array asociativo para su uso en respuestas JSON
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'totalAmount' => $this->getTotalAmount(),
            'invoiceDate' => $this->getInvoiceDate()->format('Y-m-d'),
            'invoiceNumber' => $this->getInvoiceNumber(),
            'status' => $this->getStatus(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInvoiceDate(): DateTimeInterface
    {
        return $this->invoiceDate;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @return InvoiceStatus
     */
    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }
}