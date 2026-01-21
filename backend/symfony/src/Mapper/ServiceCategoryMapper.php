<?php

namespace App\Mapper;

use App\Dto\Service\Request\ServiceCategoryRequestDto;
use App\Dto\Service\Response\ServiceCategoryResponseDto;
use App\Entity\ServiceCategory;

class ServiceCategoryMapper
{
    /**
     * Convierte una entidad ServiceCategory a un DTO de respuesta.
     *
     * @param ServiceCategory $serviceCategory
     * @return ServiceCategoryResponseDto
     */
    public function toResponseDto(ServiceCategory $serviceCategory): ServiceCategoryResponseDto
    {
        return new ServiceCategoryResponseDto(
            id: $serviceCategory->getId(),
            name: $serviceCategory->getName(),
            description: $serviceCategory->getDescription()
        );
    }

    /**
     * Convierte una colección (iterable) de entidades ServiceCategory a un array de DTOs de respuesta.
     *
     * @param iterable<ServiceCategory> $serviceCategories
     * @return ServiceCategoryResponseDto[]
     */
    public function toResponseDtoCollection(iterable $serviceCategories): array
    {
        $dtos = [];
        foreach ($serviceCategories as $serviceCategory) {
            $dtos[] = $this->toResponseDto($serviceCategory);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad ServiceCategory.
     *
     * @param ServiceCategoryRequestDto $dto
     * @return ServiceCategory
     */
    public function toEntity(
        ServiceCategoryRequestDto $dto
    ): ServiceCategory
    {
        $serviceCategory = new ServiceCategory();
        $serviceCategory->setName($dto->name);
        $serviceCategory->setDescription($dto->description);
        return $serviceCategory;
    }

    /**
     * Actualiza una entidad ServiceCategory existente con datos de un DTO de solicitud.
     *
     * @param ServiceCategory $serviceCategory
     * @param ServiceCategoryRequestDto $dto
     * @return void
     */
    public function updateEntity(ServiceCategory $serviceCategory, ServiceCategoryRequestDto $dto): void
    {
        if (isset($dto->name)) {
            $serviceCategory->setName($dto->name);
        }
        if (isset($dto->description)) {
            $serviceCategory->setDescription($dto->description);
        }
    }
}