<?php

namespace App\Controller\API\Pet;

use App\Dto\Pet\PetCreateRequestDto;
use App\Dto\Pet\PetResponseDto;
use App\Dto\Pet\PetUpdateRequestDto;
use App\Entity\Pet;
use App\Entity\PetType;
use App\Entity\User;
use App\Enum\Gender;
use App\Mapper\PetMapper;
use App\Repository\PetRepository;
use App\Repository\PetTypeRepository;
use App\Repository\UserRepository;
use App\Response\ApiJsonResponse;
use App\Security\Voter\PetVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pets', name: 'api_pets_')]
class PetController extends AbstractController
{
 public function __construct(
     private readonly UserRepository $userRepository,
     private readonly PetRepository $petRepository,
     private readonly PetTypeRepository $petTypeRepository,
     private readonly PetMapper $petMapper
 ){}

    /**
     * Listar mascotas.
     * Si es un cliente, solo muestra sus mascotas activas.
     * Si es un admin / vet, muestra todas las mascotas.
     * Se puede filtrar por mascota activa: ?is_active=true/false si es para admin / vet. Si no se especifica, se muestran todas las mascotas.
     * En el caso de un cliente solo se muestran sus mascotas activas
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('', name: 'find_all', methods: ['GET'])]
    public function findAll(Request $request) : JsonResponse
    {
        $user = $this->getUser();

        // Obtiene el parámetro de filtro de mascotas activas si existe
        $isActive = $request->query->has('is_active')
            ? $request->query->getBoolean('is_active') : null;

        $criteria = [];
        if ($isActive !== null) {
            $criteria['isActive'] = $isActive;
        }

        if ($user instanceof User && $user->isClient()) {
            $pets = $this->petRepository->findBy(['client' => $user, 'isActive' => true]);
        } else {
            $pets = $this->petRepository->findBy($criteria);
        }

        return ApiJsonResponse::success($this->petMapper->toResponseDtoCollection($pets));
    }

    /**
     * Encuentra una mascota por su ID
     * Solo permite ver la mascota si el usuario es el cliente dueño de la mascota, o si es admin / vet
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/{id}', name: 'find_by_id', methods: ['GET'])]
    public function findById(int $id) : JsonResponse
    {
        $pet = $this->petRepository->find($id);

        // Verifica si la mascota existe
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // El PetVoter::VIEW manejará si el usuario es el dueño o es admin / vet
        $this->denyAccessUnlessGranted(PetVoter::VIEW, $pet);

        return ApiJsonResponse::success($this->petMapper->toResponseDto($pet));
    }

    /**
     * Crea una nueva mascota
     * Solo los usuarios con permiso de crear mascotas (admin, vet) pueden acceder
     * @param PetCreateRequestDto $dto
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] PetCreateRequestDto $dto
    ) : JsonResponse
    {

        $this->denyAccessUnlessGranted(PetVoter::CREATE, new Pet());

        // Solo comprobamos unicidad si viene microchip
        if ($dto->microchip !== null) {
            $existingPet = $this->petRepository->findOneBy(['microchip' => $dto->microchip]);

            if ($existingPet) {
                return ApiJsonResponse::error(
                    'El microchip ya está en uso.',
                    Response::HTTP_CONFLICT
                ); // Comentario: evita duplicados de microchip no nulo
            }
        }

        $client = $this->userRepository->find($dto->clientId);
        if (!$client) {
            return ApiJsonResponse::error('Cliente no encontrado');
        }

        $petType = $this->petTypeRepository->find($dto->petTypeId);
        if (!$petType instanceof PetType) {
            return ApiJsonResponse::error('Tipo de mascota no encontrado');
        }

        $newPet = $this->petMapper->toEntity($dto, $client, $petType);

        $this->petRepository->save($newPet);

        return ApiJsonResponse::success('Pet created successfully', Response::HTTP_CREATED);
    }

    /**
     * Elimina una mascota por su ID
     * Solo los usuarios con permiso de eliminar mascotas (admin, vet) pueden acceder
     * @param int $id
     * @return JsonResponse/
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Busca la mascota por su ID
        $pet = $this->petRepository->find($id);

        // Verifica si la mascota existe
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // Determina si el usuario tiene permiso para eliminar la mascota
        $this->denyAccessUnlessGranted(PetVoter::DELETE, $pet);

        // eliminamos la mascota (hard delete)
        $pet->setIsActive(false);
        $this->petRepository->remove($pet);

        return ApiJsonResponse::success('Pet deleted successfully');
    }

    /**
     * Desactiva/Reactiva una mascota por su ID
     * Solo los usuarios con permiso de reactivar mascotas (admin, vet) pueden acceder
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['PATCH'])]
    public function toggleActive(int $id): JsonResponse
    {
        $pet = $this->petRepository->find($id);

        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // Determina si el usuario tiene permiso para eliminar la mascota
        $this->denyAccessUnlessGranted(PetVoter::UPDATE, $pet);

        // cambiamos el estado de la mascota
        $pet->isActive() ? $pet->setIsActive(false) : $pet->setIsActive(true);

        $this->petRepository->save($pet);

        return ApiJsonResponse::success('Pet with id: '. $pet->getId() . ' successfully ' . ($pet->isActive() ? 'reactivated' : 'deactivated'));
    }

    /**
     * Desactiva una mascota por su ID
     * Solo los usuarios con permiso de desactivar mascotas (admin, vet) pueden acceder
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}/deactivate', name: 'deactivate', methods: ['PATCH'])]
    public function deactivatePet(int $id): JsonResponse
    {
        $pet = $this->petRepository->find($id);

        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // Determina si el usuario tiene permiso para eliminar la mascota
        $this->denyAccessUnlessGranted(PetVoter::UPDATE, $pet);

        if (!$pet->isActive()) {
            return ApiJsonResponse::success();
        }

        // cambiamos el estado de la mascota
        $pet->setIsActive(false);

        $this->petRepository->save($pet);

        return ApiJsonResponse::success();
    }

    /**
     * Activa una mascota por su ID
     * Solo los usuarios con permiso de activar mascotas (admin, vet) pueden acceder
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}/activate', name: 'activate', methods: ['PATCH'])]
    public function activatePet(int $id): JsonResponse
    {
        $pet = $this->petRepository->find($id);

        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // Determina si el usuario tiene permiso para eliminar la mascota
        $this->denyAccessUnlessGranted(PetVoter::UPDATE, $pet);

        if ($pet->isActive()) {
            return ApiJsonResponse::success();
        }

        // cambiamos el estado de la mascota
        $pet->setIsActive(true);

        $this->petRepository->save($pet);

        return ApiJsonResponse::success();
    }

    /**
     * Actualiza una mascota por su ID
     * Solo los usuarios con permiso de actualizar mascotas (admin, vet) pueden acceder
     * @param int $id
     * @param PetUpdateRequestDto $dto
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] PetUpdateRequestDto $dto
    ): JsonResponse
    {
        $pet = $this->petRepository->find($id);
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error("Pet not found", Response::HTTP_NOT_FOUND);
        }

        // Determina si el usuario tiene permiso para eliminar la mascota
        $this->denyAccessUnlessGranted(PetVoter::UPDATE, $pet);

        if ($dto->clientId !== null) {
            $client = $this->userRepository->find($dto->clientId);
            if (!$client instanceof User) {
                return ApiJsonResponse::error("Client not found", Response::HTTP_NOT_FOUND);
            }
            $pet->setClient($client);
        }

        if ($dto->petTypeId !== null) {
            $petType = $this->petTypeRepository->find($dto->petTypeId);
            if (!$petType instanceof PetType) {
                return ApiJsonResponse::error("Pet type not found", Response::HTTP_NOT_FOUND);
            }
            $pet->setPetType($petType);
        }

        if ($dto->microchip !== null) {
            if ($dto->microchip !== $pet->getMicrochip()) {
                $conflict = $this->petRepository->findOneBy(['microchip' => $dto->microchip]);
                if ($conflict && $conflict->getId() !== $pet->getId()) {
                    return ApiJsonResponse::error('El microchip ya está en uso.', Response::HTTP_CONFLICT); // Comentario: Unicidad microchip
                }
            }
            $pet->setMicrochip($dto->microchip);
        }

        $this->petMapper->updateEntity($pet, $dto);

        $this->petRepository->save($pet);

        return ApiJsonResponse::success('Pet with Id ' . $pet->getId() . ', updated successfully');
    }
}