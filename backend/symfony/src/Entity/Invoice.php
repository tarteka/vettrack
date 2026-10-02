<?php

namespace App\Entity;

use App\Enum\InvoicePaymentMethod;
use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'invoices')]
#[ORM\Index(name: 'idx_user_invoices', columns: ['user_id'])]
#[ORM\Index(name: 'idx_pet_invoices', columns: ['pet_id'])]
#[ORM\Index(name: 'idx_invoice_number', columns: ['invoice_number'])]
#[ORM\Index(name: 'idx_status', columns: ['status'])]
#[UniqueEntity(
    fields: ['invoice_number'],
    message: 'Este número de factura ya existe.',
)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(name: 'pet_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private Pet $pet;

    #[ORM\ManyToOne(targetEntity: MedicalRecord::class)]
    #[ORM\JoinColumn(name: 'medical_record_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MedicalRecord $medicalRecord = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $invoiceDate;

    #[ORM\Column(type: 'string', length: 9, unique: true, nullable: true)]
    private ?string $invoiceNumber = null;

    // https://www.doctrine-project.org/projects/doctrine-orm/en/3.5/cookbook/generated-columns.html#declaring-a-generated-column
    #[ORM\Column(
        type: 'date',
        nullable: true,
        insertable: false,
        updatable: false,
        columnDefinition: "DATE GENERATED ALWAYS AS (DATE_ADD(invoice_date, INTERVAL 1 MONTH)) STORED",
        generated: 'ALWAYS'
    )]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $subtotal = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $taxAmount = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalAmount = 0;

    #[ORM\Column(type: 'string', length: 10, enumType: InvoiceStatus::class, options: ['default' => InvoiceStatus::Pending])]
    private InvoiceStatus $status = InvoiceStatus::Pending;

    #[ORM\Column(type: 'string', length: 20, nullable: true, enumType: InvoicePaymentMethod::class)]
    private ?InvoicePaymentMethod $paymentMethod = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\OneToMany(targetEntity: InvoiceItem::class, mappedBy: "invoice", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoiceItems;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->invoiceItems = new ArrayCollection();
        $this->setInvoiceDate(new \DateTime());
        $this->setSubtotal(0.0);
        $this->setTaxAmount(0.0);
        $this->setTotalAmount(0.0);
    }

    // Getters y setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;
        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMedicalRecord(): ?MedicalRecord
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecord $medicalRecord): self
    {
        $this->medicalRecord = $medicalRecord;
        return $this;
    }

    public function getInvoiceDate(): \DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): self
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function setInvoiceNumber(string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): self
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): self
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPaymentMethod(): ?InvoicePaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?InvoicePaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPaymentDate(): ?DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate ?? new \DateTimeImmutable();
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return Collection|InvoiceItem[]
     */
    public function getInvoiceItems(): Collection|array
    {
        return $this->invoiceItems;
    }

    public function addInvoiceItem(InvoiceItem $item): self
    {
        if (!$this->invoiceItems->contains($item)) {
            $this->invoiceItems[] = $item;
            $item->setInvoice($this);
        }
        return $this;
    }

    public function removeInvoiceItem(InvoiceItem $item): self
    {
        if ($this->invoiceItems->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInvoice() === $this) {
                $item->setInvoice(null);
            }
        }
        return $this;
    }

    public function calculateSubtotal(): float
    {
        $subtotal = 0;
        foreach ($this->invoiceItems as $item) {
            $subtotal += $item->getSubTotal();
        }
        return $subtotal;
    }

    public function calculateTaxAmount(): float
    {
        $taxAmount = 0;
        foreach ($this->invoiceItems as $item) {
            $taxAmount += $item->getTaxAmount();
        }
        return $taxAmount;
    }

    public function calculateTotalAmount(): float
    {
        return $this->calculateSubtotal() + $this->calculateTaxAmount();
    }

    /**
     * Actualiza los totales (subtotal, taxAmount, totalAmount) con base en las líneas
     */
    public function updateTotals(): self
    {
        $this->subtotal = round($this->calculateSubtotal(),2);
        $this->taxAmount = round($this->calculateTaxAmount(),2);
        $this->totalAmount = round($this->calculateTotalAmount(),2);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Verifica si la factura puede ser eliminada.
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return in_array(
            $this->status,
            [InvoiceStatus::Pending, InvoiceStatus::Overdue],
            true);
    }

    /**
     * Marca la factura como pagada.
     * @throws DomainException
     */
    public function markAsPaid(
        InvoicePaymentMethod $paymentMethod = InvoicePaymentMethod::Cash,
        ?DateTimeInterface $paymentDate = null
    ): void
    {
        if (!in_array($this->status, [InvoiceStatus::Pending, InvoiceStatus::Overdue], true)) {
            throw new DomainException('No se puede marcar la factura como pagada.');
        }

        $this->setStatus(InvoiceStatus::Paid);
        $this->setPaymentMethod($paymentMethod);
        $this->setPaymentDate($paymentDate);
    }

    /**
     * Marca la factura como cancelada.
     * @throws DomainException
     */
    public function markAsCancelled(): void
    {
        if ($this->status === InvoiceStatus::Paid) {
            throw new \DomainException('No se puede marcar la factura como cancelada.');
        }

        $this->setStatus(InvoiceStatus::Cancelled);
    }
}
