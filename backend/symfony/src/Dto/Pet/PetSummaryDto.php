<?php

namespace App\Dto\Pet;

use App\Entity\Pet;
use App\Entity\PetType;
use App\Enum\Gender;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * Data Transfer Object para el dashboard de mascotas
 */
class PetSummaryDto
{
    private int $id;
    private string $name;
    private string $petType;
    private string $breeze;

    private string $age;
    private Gender $gender;
    private string $chip;
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeInterface $lastAppointment;

    public function __construct(Pet $pet, ?\DateTimeInterface $lastAppointment)
    {
        $this->id = $pet->getId();
        $this->name = $pet->getName();
        $this->petType = $pet->getPetType()->getName();
        $this->breeze = $pet->getBreed() ?? 'Desconocida';
        $this->age = $pet->getAge();
        $this->gender = $pet->getGender();
        $this->chip = $pet->getMicrochip() ?? '-';
        $this->lastAppointment = $lastAppointment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPetType(): string
    {
        return $this->petType;
    }

    public function getBreeze(): string
    {
        return $this->breeze;
    }

    public function getAge(): string
    {
        return $this->age;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function getChip(): string
    {
        return $this->chip;
    }

    public function getLastAppointment(): ?\DateTimeInterface
    {
        return $this->lastAppointment;
    }
}