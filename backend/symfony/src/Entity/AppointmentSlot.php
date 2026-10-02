<?php

namespace App\Entity;

use App\Repository\AppointmentSlotRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AppointmentSlot Entity - Representa los turnos disponibles para citas veterinarias
 */

#[ORM\Entity(repositoryClass: AppointmentSlotRepository::class)]
#[ORM\Table(
    name: 'appointment_slots',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'unique_slot', columns: ['slot_date', 'slot_time'])
    ]
)]
#[ORM\Index(name: 'idx_available_slots', columns: ['slot_date', 'is_available'])]
#[UniqueEntity(fields: ['veterinarian', 'slotDate', 'slotTime'])]
class AppointmentSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private DateTimeInterface $slotDate; // Fecha del turno

    #[ORM\Column(type: 'time')]
    private DateTimeInterface $slotTime; // Hora del turno

    #[ORM\Column(type: 'integer', options: ['default' => 30])]
    private int $durationMinutes = 30; // Duración en minutos

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isAvailable = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $updatedAt = null;


    // Getters and setters...

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DateTimeInterface
     */
    public function getSlotDate(): DateTimeInterface
    {
        return $this->slotDate;
    }

    /**
     * @param DateTimeInterface $slotDate
     */
    public function setSlotDate(DateTimeInterface $slotDate): void
    {
        $this->slotDate = $slotDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getSlotTime(): DateTimeInterface
    {
        return $this->slotTime;
    }

    /**
     * @param DateTimeInterface $slotTime
     */
    public function setSlotTime(DateTimeInterface $slotTime): void
    {
        $this->slotTime = $slotTime;
    }

    /**
     * @return int
     */
    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    /**
     * @param int $durationMinutes
     */
    public function setDurationMinutes(int $durationMinutes): void
    {
        $this->durationMinutes = $durationMinutes;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Combina slotDate y slotTime en un solo DateTimeInterface
     *
     * @return DateTimeInterface
     */
    public function getDateTime(): DateTimeInterface
    {
        $dateTime = clone $this->slotDate;
        $dateTime->setTime(
            $this->slotTime->format('H'),
            $this->slotTime->format('i'),
            $this->slotTime->format('s')
        );
        return $dateTime;
    }

    /**
     * Marca el turno como reservado.
     *
     * @throws DomainException Si el turno ya está reservado.
     */
    public function reserve(): void
    {
        if (!$this->isAvailable) {
            throw new DomainException('El turno ya está reservado.');
        }

        $this->isAvailable = false;
    }

    /**
     * Libera el turno.
     */
    public function release(): void
    {
        $this->setIsAvailable(true);
    }


}
