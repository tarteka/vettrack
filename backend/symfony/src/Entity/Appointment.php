<?php

namespace App\Entity;

use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Appointment Entity - Representa citas veterinarias para mascotas
 */

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ORM\Table(name: 'appointments')]
#[ORM\UniqueConstraint(name: 'unique_appointment_slot', columns: ['appointment_slot_id'])]
#[ORM\Index(name: 'idx_pet_appointments', columns: ['pet_id'])]
#[ORM\Index(name: 'idx_vet_appointments', columns: ['veterinarian_id'])]
#[ORM\Index(name: 'idx_status', columns: ['status'])]
#[ORM\Index(name: 'idx_created_by', columns: ['created_by'])]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(name: 'pet_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Pet $pet;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'veterinarian_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $veterinarian = null;

    #[ORM\ManyToOne(targetEntity: AppointmentSlot::class)]
    #[ORM\JoinColumn(name: 'appointment_slot_id', referencedColumnName: 'id', nullable: false)]
    private AppointmentSlot $appointmentSlot;

    #[ORM\ManyToOne(targetEntity: AppointmentType::class)]
    #[ORM\JoinColumn(name: 'appointment_type_id', referencedColumnName: 'id', nullable: false)]
    private AppointmentType $appointmentType;

    #[ORM\Column(type: 'text')]
    private string $reason;

    #[ORM\Column(type: 'string', length: 20, enumType: AppointmentStatus::class, options: ['default' => AppointmentStatus::Confirmed])]
    private AppointmentStatus $status = AppointmentStatus::Confirmed;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVeterinarian(): ?User
    {
        return $this->veterinarian;
    }

    public function setVeterinarian(?User $veterinarian): self
    {
        $this->veterinarian = $veterinarian;
        return $this;
    }

    public function getAppointmentSlot(): AppointmentSlot
    {
        return $this->appointmentSlot;
    }

    public function setAppointmentSlot(AppointmentSlot $appointmentSlot): self
    {
        $this->appointmentSlot = $appointmentSlot;
        return $this;
    }

    public function getAppointmentType(): AppointmentType
    {
        return $this->appointmentType;
    }

    public function setAppointmentType(AppointmentType $appointmentType): self
    {
        $this->appointmentType = $appointmentType;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getStatus(): AppointmentStatus
    {
        return $this->status;
    }

    public function setStatus(AppointmentStatus $status): self
    {
        $this->status = $status;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Verifica si la cita puede ser cancelada.
     */
    public function isActive(): bool
    {
        return in_array(
            $this->status,
            [AppointmentStatus::Requested, AppointmentStatus::Confirmed],
            true
        );
    }

    /**
     * Confirma la cita asignando un veterinario y cambiando el estado a Confirmado.
     *
     * @param User $veterinarian El veterinario que confirma la cita.
     * @return void
     */
    public function confirmAppointment(User $veterinarian): void
    {
        $this->veterinarian = $veterinarian;
        $this->status = AppointmentStatus::Confirmed;
    }

    /**
     * Confirma la cita asignando un veterinario y cambiando el estado a Confirmado.
     * Solo se pueden confirmar citas en estado Requested.
     *
     * @param User $vet El veterinario que confirma la cita.
     * @return void
     * @throws DomainException
     */
    public function confirm(User $vet): void
    {
        if ($this->status !== AppointmentStatus::Requested) {
            throw new DomainException('Estado invalido para confirmar la cita.');
        }
        $this->setVeterinarian($vet);
        $this->setStatus(AppointmentStatus::Confirmed);
    }

    /**
     * Cancela la cita. Solo se pueden cancelar citas en estado Programmed o Confirmed.
     *
     * @return void
     * @throws DomainException
     */
    public function cancelByClinic(): void
    {
        if (!$this->isActive()) {
            throw new \DomainException('La cita no se puede cancelar.');
        }

        $this->status = AppointmentStatus::Cancelled;
    }

    /**
     * Cancela la cita solicitada por el cliente. Solo se pueden cancelar citas en estado Requested.
     *
     * @return void
     * @throws DomainException
     */
    public function cancelByClient(): void
    {
        if ($this->status !== AppointmentStatus::Requested) {
            throw new DomainException(
                'Solo se pueden cancelar citas solicitadas.'
            );
        }

        $this->status = AppointmentStatus::Cancelled;
    }
}
