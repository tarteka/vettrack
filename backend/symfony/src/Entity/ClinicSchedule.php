<?php

namespace App\Entity;

use App\Repository\ClinicScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ClinicScheduleRepository::class)]
#[ORM\Table(name: 'clinic_schedules')]
class ClinicSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    private int $dayOfWeek;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $startTime;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $endTime;

    #[ORM\Column(type: 'integer', options: ['default' => 30])]
    private int $slotDurationMinutes = 30;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;


    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): self
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new \InvalidArgumentException('El día de la semana debe estar entre 1 (lunes) y 7 (domingo).');
        }

        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        if ($endTime <= $this->startTime) {
            throw new \InvalidArgumentException('La hora de fin debe ser posterior a la de inicio.');
        }

        $this->endTime = $endTime;
        return $this;
    }

    public function getSlotDurationMinutes(): int
    {
        return $this->slotDurationMinutes;
    }

    public function setSlotDurationMinutes(int $slotDurationMinutes): self
    {
        if ($slotDurationMinutes <= 0) {
            throw new \InvalidArgumentException('La duración del slot debe ser positiva.');
        }

        $this->slotDurationMinutes = $slotDurationMinutes;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
}