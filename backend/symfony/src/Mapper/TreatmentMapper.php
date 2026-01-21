<?php

namespace App\Mapper;

use App\Dto\Treatment\Request\TreatmentRequestDto;
use App\Dto\Treatment\Response\TreatmentResponseDto;
use App\Entity\MedicalRecord;
use App\Entity\Pet;
use App\Entity\Treatment;
use App\Entity\User;
use Exception;

class TreatmentMapper
{

    /**
     * Convierte una entidad Treatment a un DTO de respuesta.
     *
     * @param Treatment $treatment
     * @return TreatmentResponseDto
     */
    public function toResponseDto(Treatment $treatment) : TreatmentResponseDto
    {
        return new TreatmentResponseDto(
            id: $treatment->getId(),
            name: $treatment->getName(),
            medicine: $treatment->getMedicine(),
            dose: $treatment->getDose(),
            frequency: $treatment->getFrequency(),
            instructions: $treatment->getInstructions(),
            startDate: $treatment->getStartDate()->format('d-m-Y'),
            endDate: $treatment->getEndDate()?->format('d-m-Y'),
            veterinarianFullName: $treatment->getVeterinarian()->getFullName(),
            duration: $treatment->getDuration(),
            status: $treatment->getStatus()->value,
            suspendedReason: $treatment->getSuspendedReason() ?? ''
        );
    }

    /**
     * Convierte una colección (iterable) de Treatment a un array de DTOs.
     *
     * @param iterable<Treatment> $treatments
     * @return TreatmentResponseDto[]
     */
    public function toResponseDtoCollection(iterable $treatments): array
    {
        $dtos = [];
        foreach ($treatments as $treatment) {
            $dtos[] = $this->toResponseDto($treatment);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad Treatment.
     *
     * @param TreatmentRequestDto $treatmentRequestDto
     * @param Pet $pet
     * @param User $veterinarian
     * @param MedicalRecord $medicalRecord
     * @return Treatment
     * @throws Exception
     */
    public function toEntity(
        TreatmentRequestDto $treatmentRequestDto,
        Pet $pet,
        User $veterinarian,
        MedicalRecord $medicalRecord
    ): Treatment
    {
        $treatment = new Treatment();
        $treatment->setName($treatmentRequestDto->name);
        $treatment->setMedicine($treatmentRequestDto->medicine);
        $treatment->setDose($treatmentRequestDto->dose);
        $treatment->setFrequency($treatmentRequestDto->frequency);
        $treatment->setInstructions($treatmentRequestDto->instructions);
        $treatment->setStartDate(new \DateTimeImmutable($treatmentRequestDto->startDate));
        if ($treatmentRequestDto->endDate !== null) {
            $treatment->setEndDate(new \DateTimeImmutable($treatmentRequestDto->endDate));
        }
        $treatment->setPet($pet);
        $treatment->setVeterinarian($veterinarian);
        $treatment->setMedicalRecord($medicalRecord);
        $treatment->setSuspendedReason($treatmentRequestDto->suspendedReason);

        return $treatment;
    }


    /**
     * Actualiza una entidad Treatment existente con datos de un DTO de solicitud.
     *
     * @param Treatment $treatment
     * @param TreatmentRequestDto $dto
     * @return Treatment
     * @throws Exception
     */
    public function updateEntity(Treatment $treatment, TreatmentRequestDto $dto) : Treatment
    {
        if (isset($dto->name)) {
            $treatment->setName($dto->name);
        }

        if (isset($dto->medicine)) {
            $treatment->setMedicine($dto->medicine);
        }

        if (isset($dto->dose)) {
            $treatment->setDose($dto->dose);
        }

        if (isset($dto->frequency)) {
            $treatment->setFrequency($dto->frequency);
        }

        if (isset($dto->instructions)) {
            $treatment->setInstructions($dto->instructions);
        }

        if (isset($dto->startDate)) {
            $treatment->setStartDate(new \DateTimeImmutable($dto->startDate));
        }

        // Manejo especial para endDate, ya que puede ser null explícitamente
        // Si endDate está presente en el DTO, actualizamos el valor
        // incluso si es null
        if (array_key_exists('endDate', (array)$dto)) {
            if ($dto->endDate !== null) {
                $treatment->setEndDate(new \DateTimeImmutable($dto->endDate));
            } else {
                $treatment->setEndDate(null);
            }
        }

        // suspendedReason: puede ser string o null
        if (array_key_exists('suspendedReason', (array) $dto)) {
            $treatment->setSuspendedReason($dto->suspendedReason);
        }

        return $treatment;
    }
}