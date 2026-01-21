<?php

namespace App\Mapper;

use App\Dto\MedicalRecord\Request\MedicalRecordRequestDto;
use App\Dto\MedicalRecord\Response\MedicalRecordResponseDto;
use App\Entity\MedicalRecord;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MedicalRecordType;

class MedicalRecordMapper
{
    /**
     * Convierte una entidad MedicalRecord a un DTO de respuesta.
     *
     * @param MedicalRecord $record
     * @return MedicalRecordResponseDto
     */
    public function toResponseDto(MedicalRecord $record): MedicalRecordResponseDto
    {
        return new MedicalRecordResponseDto(
            id: $record->getId(),
            petId: $record->getPet()->getId(),
            type: $record->getType()->value,
            description: $record->getDescription(),
            veterinarian: $record->getVeterinarian()->getFullName(),
            diagnosis: $record->getDiagnosis(),
            notes: $record->getNotes(),
            procedures: $record->getProcedures(),
            date: $record->getRecordDate()->format('d-m-Y')
        );
    }

    /**
     * Convierte una colección (iterable) de MedicalRecord a un array de DTOs.
     *
     * @param iterable<MedicalRecord> $records
     * @return MedicalRecordResponseDto[]
     */
    public function toResponseDtoCollection(iterable $records): array
    {
        $dtos = [];
        foreach ($records as $record) {
            $dtos[] = $this->toResponseDto($record);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad MedicalRecord.
     *
     * @param MedicalRecordRequestDto $dto
     * @param Pet $pet
     * @param User $veterinarian
     * @return MedicalRecord
     */
    public function toEntity(
        MedicalRecordRequestDto $dto,
        Pet $pet,
        User $veterinarian
    ): MedicalRecord {
        $medicalRecord = new MedicalRecord();

        $medicalRecord->setPet($pet);
        $medicalRecord->setVeterinarian($veterinarian);
        $medicalRecord->setType(MedicalRecordType::from($dto->type));
        $medicalRecord->setDiagnosis($dto->diagnosis);
        $medicalRecord->setDescription($dto->description);
        $medicalRecord->setProcedures($dto->procedures);
        $medicalRecord->setNotes($dto->notes);
        $medicalRecord->setRecordDate(new \DateTime());

        return $medicalRecord;
    }

    /**
     * Actualiza una entidad MedicalRecord existente con datos de un DTO de solicitud.
     *
     * @param MedicalRecord $medicalRecord
     * @param MedicalRecordRequestDto $dto
     * @return MedicalRecord
     */
    public function updateEntity(MedicalRecord $medicalRecord, MedicalRecordRequestDto $dto): MedicalRecord
    {
        if (isset($dto->type)) {
            $medicalRecord->setType(MedicalRecordType::from($dto->type));
        }

        if (isset($dto->diagnosis)) {
            $medicalRecord->setDiagnosis($dto->diagnosis);
        }

        if (isset($dto->description)) {
            $medicalRecord->setDescription($dto->description);
        }

        if (isset($dto->procedures)) {
            $medicalRecord->setProcedures($dto->procedures);
        }

        if (isset($dto->notes)) {
            $medicalRecord->setNotes($dto->notes);
        }

        return $medicalRecord;
    }

}