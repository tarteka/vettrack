<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_items')]
#[ORM\Index(name: 'idx_invoice_items', columns: ['invoice_id'])]
class InvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'invoiceItems')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Invoice $invoice;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private Service $service ;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $subTotal = 0.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $taxAmount = 0.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalAmount = 0.0;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * Recalcula TotalPrice y TotalPriceTax
     */
    public function updatePrices(): void
    {
        $this->subTotal = round($this->quantity * $this->service->getUnitPrice(),2);
        $this->taxAmount = round($this->subTotal * ($this->service->getTaxRate() / 100),2);
        $this->totalAmount = round($this->subTotal + $this->taxAmount, 2);
    }

    // Getters y setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;
        $this->updatePrices();
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        $this->quantity = $quantity;
        $this->updatePrices();
        return $this;
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
