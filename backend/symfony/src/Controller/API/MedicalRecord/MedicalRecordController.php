<?php

namespace App\Controller\API\MedicalRecord;

use App\Dto\MedicalRecord\Request\MedicalRecordRequestDto;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\MedicalRecord;
use App\Mapper\MedicalRecordMapper;
use App\Repository\MedicalRecordRepository;
use App\Repository\PetRepository;
use App\Response\ApiJsonResponse;
use App\Security\Voter\MedicalRecordVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pets/{petId}/medical-records', name: 'medical_records_')]
class MedicalRecordController extends AbstractController
{
    public function __construct(
        private readonly MedicalRecordRepository $medicalRecordRepository,
        private readonly PetRepository           $petRepository,
        private readonly MedicalRecordMapper     $medicalRecordMapper
    ){}


    /**
     * Encuentra todos los registros médicos asociados a una mascota específica.
     * - Admin / Vet: acceden a cualquier mascota
     * - Cliente: solo a sus propias mascotas
     * @param int $petId
     * @return JsonResponse
     */
    #[Route('', name: 'find_all', methods: ['GET'] )]
    public function findAllByPetId(int $petId): JsonResponse
    {
        $user = $this->getUser();

        if (! $user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $pet = $this->petRepository->find($petId);
        if (!$pet) {
            return ApiJsonResponse::error('Mascota no encontrada', Response::HTTP_NOT_FOUND);
        }

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VET')) {
            $medicalRecords = $this->medicalRecordRepository->findAllByPetId($petId);
        } elseif ($this->isGranted('ROLE_CLIENT')) {
            // El cliente solo puede ver registros de sus propias mascotas
            if ($pet->getClient() === null || $pet->getClient()->getId() !== $user->getId()) {
                return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
            }
            $medicalRecords = $this->medicalRecordRepository->findAllByPetId($petId);
        } else {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $medicalRecordsResponse = $this->medicalRecordMapper->toResponseDtoCollection($medicalRecords);

        return ApiJsonResponse::success($medicalRecordsResponse);
    }

    /**
     * Encuentra un registro médico específico por ID para una mascota específica.
     * - Admin / Vet: acceden a cualquier mascota
     * - Cliente: solo a sus propias mascotas
     * @param int $petId
     * @param int $recordId
     * @return JsonResponse
     */
    #[Route('/{recordId}', name: 'find_one', methods: ['GET'] )]
    public function findOneById(int $petId, int $recordId): JsonResponse
    {
        $medicalRecord = $this->medicalRecordRepository->find($recordId);
        if (!$medicalRecord instanceof MedicalRecord || $medicalRecord->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('Registro médico no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(MedicalRecordVoter::VIEW, $medicalRecord);

        return ApiJsonResponse::success(
            $this->medicalRecordMapper->toResponseDto($medicalRecord)
        );
    }

    /**
     * Crea un nuevo registro médico para una mascota específica.
     * - Vet: puede crear registros para cualquier mascota
     * - Cliente / Admin: no tienen permisos para crear registros médicos
     * @param int $petId
     * @param MedicalRecordRequestDto $dto
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'] )]
    #[IsGranted('ROLE_VET')]
    public function newMedicalRecord(
        int $petId,
        #[MapRequestPayload] MedicalRecordRequestDto $dto
    ): JsonResponse
    {
        $pet = $this->petRepository->find($petId);
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error('Mascota no encontrada', Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $newMedicalRecord = $this->medicalRecordMapper->toEntity($dto, $pet, $user);

        $this->medicalRecordRepository->save($newMedicalRecord);
        return ApiJsonResponse::success('Medical record created successfully', Response::HTTP_CREATED);
    }

    /**
     * Actualiza un registro médico existente para una mascota específica.
     * - Vet: puede actualizar registros para cualquier mascota siempre que el registro sea suyo.
     * - Cliente / Admin: no tienen permisos para actualizar registros médicos
     * @param int $petId
     * @param int $recordId
     * @param MedicalRecordRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{recordId}', name: 'update', methods: ['PUT', 'PATCH'] )]
    #[IsGranted('ROLE_VET')]
    public function updateMedicalRecord(
        int $petId,
        int $recordId,
        #[MapRequestPayload] MedicalRecordRequestDto $dto
    ): JsonResponse
    {
        $medicalRecord = $this->medicalRecordRepository->find($recordId);

        if (!$medicalRecord instanceof MedicalRecord || $medicalRecord->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('Registro médico no encontrado', 404);
        }

        $this->denyAccessUnlessGranted(MedicalRecordVoter::UPDATE, $medicalRecord);

        $this->medicalRecordMapper->updateEntity($medicalRecord, $dto);
        $this->medicalRecordRepository->save($medicalRecord);
        return ApiJsonResponse::success($this->medicalRecordMapper->toResponseDto($medicalRecord));


    }

    /**
     * Elimina un registro médico existente para una mascota específica.
     * - Vet: puede eliminar registros para cualquier mascota siempre que el registro sea suyo.
     * - Admin: puede eliminar cualquier registro médico.
     * - Cliente: no tienen permisos para eliminar registros médicos
     * @param int $petId
     * @param int $recordId
     * @return JsonResponse
     */
    #[Route('/{recordId}', name: 'delete', methods: ['DELETE'] )]
    public function deleteMedicalRecord(
        int $petId,
        int $recordId
    ): JsonResponse
    {
        $medicalRecord = $this->medicalRecordRepository->find($recordId);
        if (!$medicalRecord instanceof MedicalRecord) {
            return ApiJsonResponse::error('Registro médico no encontrado', Response::HTTP_NOT_FOUND);
        }

        if ($medicalRecord->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('El registro médico no pertenece a la mascota especificada');
        }

        $this->denyAccessUnlessGranted(MedicalRecordVoter::DELETE, $medicalRecord);

        $this->medicalRecordRepository->remove($medicalRecord);
        return ApiJsonResponse::success('Registro médico eliminado correctamente');
    }
}