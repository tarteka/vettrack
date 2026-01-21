<?php

namespace App\Mapper;

use App\Dto\Pet\PetCreateRequestDto;
use App\Dto\Pet\PetResponseDto;
use App\Dto\Pet\PetUpdateRequestDto;
use App\Entity\Pet;
use App\Entity\PetType;
use App\Entity\User;
use App\Enum\Gender;
use App\Repository\AppointmentRepository;
use App\Repository\PetRepository;
use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use Exception;

readonly class PetMapper
{
    public function __construct(
        private PetTypeMapper $petTypeMapper,
        private readonly appointmentRepository $appointmentRepository
    ){}

    /**
     * Convierte una entidad Pet en un DTO de respuesta.
     *
     * @param Pet $pet
     * @return PetResponseDto
     * @throws Exception
     */
    public function toResponseDto(Pet $pet): PetResponseDto
    {
        $lastAppointmentDate = $this->appointmentRepository->findLastCompletedByPet($pet);

        return new PetResponseDto(
            id: $pet->getId(),
            name: $pet->getName(),
            isActive: $pet->isActive(),
            petType: $this->petTypeMapper->toResponseDto($pet->getPetType()),
            breed: $pet->getBreed(),
            birthDate: $pet->getBirthDate()?->format('Y-m-d'),
            age: $this->getAgeAsString($pet->getBirthDate()),
            gender: $pet->getGender()->value,
            color: $pet->getColor(),
            microchip: $pet->getMicrochip(),
            weight: $pet->getWeight(),
            allergies: $pet->getAllergies(),
            sterilized: $pet->getSterilized(),
            insuranceProvider: $pet->getInsuranceProvider(),
            insurancePolicyNumber: $pet->getInsurancePolicyNumber(),
            lastAppointmentDate: $lastAppointmentDate?->format('d-m-Y'),
            notes: $pet->getNotes(),
            client: [
                'id' => $pet->getClient()->getId(),
                'fullName' => $pet->getClient()->getFullName(),
                'email' => $pet->getClient()->getEmail(),
                'phone' => $pet->getClient()->getPhone(),
                'dni' => $pet->getClient()->getDni()
            ]
        );
    }

    /**
     * Convierte una colección (iterable) de Pets a un array de DTOs de respuesta.
     *
     * @param iterable<Pet> $pets
     * @return PetResponseDto[]
     * @throws Exception
     */
    public function toResponseDtoCollection(iterable $pets): array
    {
        $dtos = [];
        foreach ($pets as $pet) {
            $dtos[] = $this->toResponseDto($pet);
        }
        return $dtos;
    }

    /**
     * Convierte un DTO de solicitud a una entidad Pet.
     *
     * @param PetCreateRequestDto $dto
     * @param User $client
     * @param PetType $petType
     * @return Pet
     * @throws Exception
     */
    public function toEntity(
        PetCreateRequestDto $dto,
        User $client,
        PetType $petType
    ): Pet
    {
        $newPet = new Pet();
        $newPet->setIsActive(true);
        $newPet->setClient($client);
        $newPet->setPetType($petType);
        $newPet->setName($dto->name);
        $newPet->setBreed($dto->breed);
        $newPet->setBirthDate(new \DateTime($dto->birthDate));
        $newPet->setGender(Gender::From($dto->gender));
        $newPet->setColor($dto->color);
        $newPet->setMicrochip($dto->microchip);
        $newPet->setWeight($dto->weight);
        $newPet->setAllergies($dto->allergies);
        $newPet->setSterilized($dto->sterilized);
        $newPet->setInsuranceProvider($dto->insuranceProvider);
        $newPet->setInsurancePolicyNumber($dto->insurancePolicyNumber);
        $newPet->setNotes($dto->notes);

        return $newPet;
    }

    public function updateEntity(Pet $pet, PetUpdateRequestDto $dto): void
    {
        if ($dto->name !== null) {
            $pet->setName($dto->name);
        }

        if ($dto->breed !== null) {
            $pet->setBreed($dto->breed);
        }

        if ($dto->birthDate !== null) {
            try {
                $pet->setBirthDate(new DateTimeImmutable($dto->birthDate));
            } catch (\Exception) {
                throw new DomainException('Formato de fecha inválido (YYYY-MM-DD)');
            }
        }

        if ($dto->gender !== null) {
            try {
                $pet->setGender(Gender::from($dto->gender));
            } catch (\Exception) {
                throw new DomainException('Género debe ser macho o hembra');
            }
        }

        if ($dto->color !== null) {
            $pet->setColor($dto->color);
        }

        if ($dto->weight !== null) {
            $pet->setWeight($dto->weight);
        }

        if ($dto->allergies !== null) {
            $pet->setAllergies($dto->allergies);
        }

        if ($dto->sterilized !== null) {
            $pet->setSterilized($dto->sterilized);
        }

        if ($dto->insuranceProvider !== null) {
            $pet->setInsuranceProvider($dto->insuranceProvider);
        }

        if ($dto->insurancePolicyNumber !== null) {
            $pet->setInsurancePolicyNumber($dto->insurancePolicyNumber);
        }

        if ($dto->notes !== null) {
            $pet->setNotes($dto->notes);
        }
    }

    /**
     * Obtenemos los años de edad de una mascota.
     *
     * @param DateTimeInterface|null $birthDate
     * @return string|null
     */
    private function getAgeAsString(?DateTimeInterface $birthDate): ?string
    {
        if (!$birthDate) {
            return null;
        }

        return (new DateTimeImmutable())->diff($birthDate)->format('%y');
    }
}