<?php

namespace App\Mapper;

use App\Dto\Service\Request\ServiceRequestDto;
use App\Dto\Service\Response\ServiceResponseDto;
use App\Dto\Service\Response\ServiceCategoryResponseDto;
use App\Entity\Service;
use App\Entity\ServiceCategory;

class ServiceMapper
{
    /**
     * Convierte un Service a un DTO de respuesta.
     *
     * @param Service $service
     * @return ServiceResponseDto
     */
    public function toResponseDto(Service $service): ServiceResponseDto
    {
        $category = $service->getCategory();

        return new ServiceResponseDto(
            id: $service->getId(),
            name: $service->getName(),
            description: $service->getDescription(),
            price: $service->getUnitPrice(),
            taxRate: $service->getTaxRate(),
            isActive: $service->isActive(),
            category: new ServiceCategoryResponseDto(
                id: $category->getId(),
                name: $category->getName(),
                description: $category->getDescription()
            )
        );
    }

    /**
     * Convierte una colección (iterable) de Services a un array de DTOs de respuesta.
     *
     * @param iterable<Service> $services
     * @return ServiceResponseDto[]
     */
    public function toResponseDtoCollection(iterable $services): array
    {
        $dtos = [];
        foreach ($services as $service) {
            $dtos[] = $this->toResponseDto($service);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad Service.
     *
     * @param ServiceRequestDto $dto
     * @param ServiceCategory $serviceCategory
     * @return Service
     */
    public function toEntity(
        ServiceRequestDto $dto,
        ServiceCategory $serviceCategory ) : Service
    {
        $service = new Service();
        $service->setName($dto->name);
        $service->setDescription($dto->description);
        $service->setUnitPrice($dto->unitPrice);
        $service->setTaxRate($dto->taxRate);
        $service->setIsActive($dto->isActive);
        $service->setCategory($serviceCategory);
        return $service;
    }

    /**
     * Actualiza una entidad Service existente con datos de un DTO de solicitud.
     *
     * @param Service $service
     * @param ServiceCategory $serviceCategory
     * @param ServiceRequestDto $dto
     * @return void
     */
    public function updateEntity(
        Service $service,
        ServiceCategory $serviceCategory,
        ServiceRequestDto $dto) : void
    {
        if (isset($dto->name)) {
            $service->setName($dto->name);
        }
        if (isset($dto->description)) {
            $service->setDescription($dto->description);
        }
        if (isset($dto->unitPrice)) {
            $service->setUnitPrice($dto->unitPrice);
        }
        if (isset($dto->taxRate)) {
            $service->setTaxRate($dto->taxRate);
        }
        if (isset($serviceCategory)) {
            $service->setCategory($serviceCategory);
        }
    }

}