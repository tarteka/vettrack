<?php

namespace App\Mapper;

use App\Dto\PetType\Request\PetTypeRequestDto;
use App\Dto\PetType\Response\PetTypeResponseDto;
use App\Entity\PetType;

class PetTypeMapper
{
    /**
     * Convierte una entidad PetType a un DTO de respuesta.
     *
     * @param PetType $petType
     * @return PetTypeResponseDto
     */
    public function toResponseDto(PetType $petType): PetTypeResponseDto
    {
        return new PetTypeResponseDto(
            $petType->getId(),
            $petType->getName(),
            $petType->getDescription()
        );
    }

    /**
     * Convierte una colección (iterable) de PetType a un array de DTOs.)
     *
     * @param iterable<PetType> $petTypes
     * @return PetTypeResponseDto[]
     */
    public function toResponseDtoCollection(iterable $petTypes): array
    {
        $dtos = [];
        foreach ($petTypes as $petType) {
            $dtos[] = $this->toResponseDto($petType);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad PetType.
     *
     * @param PetTypeRequestDto $dto
     * @return PetType
     */
    public function toEntity(PetTypeRequestDto $dto): PetType
    {
       $petType = new PetType();
       $petType->setName($dto->name);
       $petType->setDescription($dto->description);

       return $petType;
    }
}