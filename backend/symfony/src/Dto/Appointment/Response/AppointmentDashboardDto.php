<?php

namespace App\Dto\Appointment\Response;

use App\Entity\Appointment;

/**
 * Data Transfer Object para el dashboard de citas
 */
class AppointmentDashboardDto
{
    private int $id;
    private string $petName;
    private string $clientName;
    private ?string $veterinarianName;
    private string $appointmentType;
    private ?\DateTimeInterface $date;
    private \DateTimeInterface $time;


    /**
     * Constructor
     *
     * @param Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        $this->id = $appointment->getId();
        $this->petName = $appointment->getPet()->getName();
        $this->clientName = $appointment->getPet()->getClient()->getFullName();
        $this->veterinarianName = $appointment->getVeterinarian()?->getFullName();
        $this->appointmentType = $appointment->getAppointmentType()->getName();
        $this->date = $appointment->getAppointmentSlot()?->getSlotDate();
        $this->time = $appointment->getAppointmentSlot()->getSlotTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'petName' => $this->getPetName(),
            'clientName' => $this->getClientName(),
            'veterinarianName' => $this->getVeterinarianName(),
            'appointmentType' => $this->getAppointmentType(),
            'date' => $this->getDate()?->format('Y-m-d'),
            'time' => $this->getTime()->format('H:i:s'),
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
     * @return string
     */
    public function getPetName(): string
    {
        return $this->petName;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @return string|null
     */
    public function getVeterinarianName(): ?string
    {
        return $this->veterinarianName;
    }

    /**
     * @return string
     */
    public function getAppointmentType(): string
    {
        return $this->appointmentType;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

}