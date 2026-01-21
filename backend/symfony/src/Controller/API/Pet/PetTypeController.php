<?php

namespace App\Controller\API\Pet;

use App\Dto\PetType\Request\PetTypeRequestDto;
use App\Entity\PetType;
use App\Mapper\PetTypeMapper;
use App\Repository\PetTypeRepository;
use App\Response\ApiJsonResponse;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pet-types', name: 'pet_types_')]
class PetTypeController extends AbstractController
{
    public function __construct(
        private readonly PetTypeRepository $petTypeRepository,
        private readonly PetTypeMapper $petTypeMapper
    ){}

    /**
     * Devuelve todos los tipos de mascotas.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'find_all', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        $types = $this->petTypeRepository->findAll();

        return ApiJsonResponse::success($this->petTypeMapper->toResponseDtoCollection($types));
    }

    /**
     * Encuentra un tipo de mascota por su ID.
     *
     * @param int $petTypeId
     * @return JsonResponse
     */
    #[Route('/{petTypeId}', name: 'find_one', methods: ['GET'])]
    public function findOneById(int $petTypeId): JsonResponse
    {
        $petType = $this->petTypeRepository->find($petTypeId);

        if (!$petType instanceof PetType) {
            throw new DomainException('Tipo de mascota no encontrado.');
        }

        return ApiJsonResponse::success(
            $this->petTypeMapper->toResponseDto($petType)
        );
    }

    /**
     * Crea un nuevo tipo de mascota.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param PetTypeRequestDto $dto
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        #[MapRequestPayload] PetTypeRequestDto $dto
    ): JsonResponse {
        if ($this->petTypeRepository->findOneBy(['name' => $dto->name])) {
            throw new DomainException('Ya existe un tipo de mascota con ese nombre.');
        }

        $petType = $this->petTypeMapper->toEntity($dto);
        $this->petTypeRepository->save($petType);

        return ApiJsonResponse::success(
            $this->petTypeMapper->toResponseDto($petType),
            Response::HTTP_CREATED
        );
    }

    /**
     * Actualiza un tipo de mascota existente.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $petTypeId
     * @param PetTypeRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{petTypeId}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int $petTypeId,
        #[MapRequestPayload] PetTypeRequestDto $dto
    ): JsonResponse {
        $petType = $this->petTypeRepository->find($petTypeId);

        if (!$petType instanceof PetType) {
            throw new DomainException('Tipo de mascota no encontrado.');
        }

        $petType->setName($dto->name);
        $petType->setDescription($dto->description);

        $this->petTypeRepository->save($petType);

        return ApiJsonResponse::success(
            $this->petTypeMapper->toResponseDto($petType)
        );
    }

    /**
     * Elimina un tipo de mascota.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $petTypeId
     * @return JsonResponse
     */
    #[Route('/{petTypeId}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $petTypeId): JsonResponse
    {
        $petType = $this->petTypeRepository->find($petTypeId);

        if (!$petType instanceof PetType) {
            throw new DomainException('Tipo de mascota no encontrado');
        }

        $this->petTypeRepository->remove($petType);

        return ApiJsonResponse::success('Tipo de mascota eliminado correctamente');
    }
}