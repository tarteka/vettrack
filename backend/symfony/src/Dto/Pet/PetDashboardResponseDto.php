<?php

namespace App\Dto\Pet;

use App\Entity\Pet;
use App\Entity\PetType;

/**
 * Data Transfer Object para el dashboard de mascotas
 */
class PetDashboardResponseDto
{
    private int $id;
    private string $name;
    private string $petType;
    private string $breed;

    public function __construct(Pet $pet)
    {
        $this->id = $pet->getId();
        $this->name = $pet->getName();
        $this->petType = $pet->getPetType()->getName();
        $this->breed = $pet->getBreed() ?? 'Desconocida';
    }

    /**
     * Convierte el DTO a un array asociativo para su uso en respuestas JSON
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'petType' => $this->getPetType(),
            'breed' => $this->getBreed(),
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPetType(): string
    {
        return $this->petType;
    }

    /**
     * @return string
     */
    public function getBreed(): string
    {
        return $this->breed;
    }


}