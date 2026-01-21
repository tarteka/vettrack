<?php

namespace App\Controller\API\Treatment;

use App\Dto\Treatment\Request\TreatmentRequestDto;
use App\Entity\MedicalRecord;
use App\Entity\Pet;
use App\Entity\Treatment;
use App\Entity\User;
use App\Enum\TreatmentStatus;
use App\Mapper\TreatmentMapper;
use App\Repository\MedicalRecordRepository;
use App\Repository\PetRepository;
use App\Repository\TreatmentRepository;
use App\Response\ApiJsonResponse;
use App\Security\Voter\PetVoter;
use App\Security\Voter\TreatmentVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pets/{petId}/treatments', name: 'treatments_')]
class TreatmentsController extends AbstractController
{
    public function __construct(
        private readonly TreatmentRepository $treatmentRepository,
        private readonly PetRepository $petRepository,
        private readonly MedicalRecordRepository $medicalRecordRepository,
        private readonly TreatmentMapper $treatmentMapper
    ){}


    /**
     * Devuelve el número de tratamientos activos para una mascota específica.
     * - Admin / Vet: acceden a cualquier mascota
     * - Cliente: solo a sus propias mascotas
     *
     * @param int $petId
     * @return JsonResponse
     */
    #[Route('/count-active', name: 'count_active', methods: ['GET'])]
    public function CountActiveTreatments(int $petId): JsonResponse
    {
        $pet = $this->petRepository->find($petId);
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error('Mascota no encontrada', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(PetVoter::VIEW, $pet);

        $count = $this->treatmentRepository->getActiveTreatmentsNumber($pet);

        return ApiJsonResponse::success(['active_treatments' => $count]);
    }

    /**
     * Encuentra todos los tratamientos asociados a una mascota específica.
     * - Admin / Vet: acceden a cualquier mascota
     * - Cliente: solo a sus propias mascotas
     * Filtros opcionales: ?status=activo|completado|suspendido
     * @param int $petId
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'find_all', methods: ['GET'] )]
    public function findAllByPetId(int $petId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (! $user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $pet = $this->petRepository->find($petId);
        if (!$pet) {
            return ApiJsonResponse::error('Mascota no encontrada', Response::HTTP_NOT_FOUND);
        }

        if ($user->isClient()) {
            if ($pet->getClient()->getId() !== $user->getId()) {
                return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
            }
        }

        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $statusFilter = $request->query->get('status');

        $criteria = ['pet' => $pet];

        // Aplicar filtro de estado si se proporciona
        if ($statusFilter) {
            match (strtolower($statusFilter)) {
                'activo' => $criteria['status'] = TreatmentStatus::Active,
                'completado' => $criteria['status'] = TreatmentStatus::Completed,
                'suspendido' => $criteria['status'] = TreatmentStatus::Suspended,
                default => $criteria['status'] = null,
            };
            if (!isset($criteria['status'])) {
                return ApiJsonResponse::error('Filtro de estado inválido: activo, completado o suspendido');
            }

        }


        $treatments = $this->treatmentRepository->findBy($criteria, ['startDate' => 'DESC']);

        $treatmentsResponse = $this->treatmentMapper->toResponseDtoCollection($treatments);
        return ApiJsonResponse::success($treatmentsResponse);
    }

    /**
     * Encuentra un tratamiento específico por ID asociado a una mascota específica.
     * - Admin / Vet: acceden a cualquier mascota
     * - Cliente: solo a sus propias mascotas
     * @param int $petId
     * @param int $treatmentId
     * @return JsonResponse
     */
    #[Route('/{treatmentId}', name: 'find_one', methods: ['GET'] )]
    public function findOneById(int $petId, int $treatmentId): JsonResponse
    {
        $treatment = $this->treatmentRepository->find($treatmentId);
        if (!$treatment instanceof Treatment || $treatment->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('Tratamiento no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(TreatmentVoter::VIEW, $treatment);

        return ApiJsonResponse::success(
            $this->treatmentMapper->toResponseDto($treatment)
        );
    }

    /**
     * Crea un nuevo tratamiento para una mascota específica.
     * Se asigna el último registro médico de la mascota.
     * - Solo Vet pueden crear tratamientos
     * @param int $petId
     * @param TreatmentRequestDto $dto
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('', name: 'create', methods: ['POST'] )]
    #[IsGranted('ROLE_VET')]
    public function newTreatment(
        int $petId,
        #[MapRequestPayload] TreatmentRequestDto $dto
    ): JsonResponse
    {
        $pet = $this->petRepository->find($petId);
        if (!$pet instanceof Pet) {
            return ApiJsonResponse::error('Mascota no encontrada', Response::HTTP_NOT_FOUND);
        }

        $medicalRecord = $this->medicalRecordRepository->findLatestByPetId($petId);
        if (!$medicalRecord instanceof MedicalRecord) {
            return ApiJsonResponse::error('No se puede asignar tratamiento: la mascota no tiene registros médicos');
        }

        $newTreatment = $this->treatmentMapper->toEntity(
            treatmentRequestDto: $dto,
            pet: $pet,
            veterinarian: $this->getUser(),
            medicalRecord: $medicalRecord
        );

        $this->treatmentRepository->save($newTreatment);
        return ApiJsonResponse::success(
            [
                'id' => $newTreatment->getId(),
                'Tratamiento creado exitosamente'
            ],
            Response::HTTP_CREATED);
    }

    /**
     * Actualiza un tratamiento existente para una mascota específica.
     * - Vet: puede actualizar tratamientos para cualquier mascota siempre que el tratamiento sea suyo.
     * - Cliente: no tienen permisos para actualizar tratamientos
     * @param int $petId
     * @param int $treatmentId
     * @param TreatmentRequestDto $dto
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/{treatmentId}', name: 'update', methods: ['PUT', 'PATCH'] )]
    #[IsGranted('ROLE_VET')]
    public function updateTreatment(
        int $petId,
        int $treatmentId,
        #[MapRequestPayload] TreatmentRequestDto $dto
    ): JsonResponse
    {
        $treatment = $this->treatmentRepository->find($treatmentId);
        if (!$treatment instanceof Treatment || $treatment->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('Tratamiento no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(TreatmentVoter::UPDATE, $treatment);

        $this->treatmentMapper->updateEntity($treatment, $dto);
        $this->treatmentRepository->save($treatment);
        return ApiJsonResponse::success('Tratamiento actualizado exitosamente');
    }

    /**
     * Elimina un tratamiento existente para una mascota específica.
     * - Vet: puede eliminar tratamientos para cualquier mascota siempre que el tratamiento sea suyo.
     * - Admin: puede eliminar cualquier tratamiento.
     * - Cliente: no tienen permisos para eliminar tratamientos
     * @param int $petId
     * @param int $treatmentId
     * @return JsonResponse
     */
    #[Route('/{treatmentId}', name: 'delete', methods: ['DELETE'] )]
    public function deleteTreatment(
        int $petId,
        int $treatmentId
    ): JsonResponse
    {
        $treatment = $this->treatmentRepository->find($treatmentId);
        if (!$treatment instanceof Treatment || $treatment->getPet()->getId() !== $petId) {
            return ApiJsonResponse::error('Tratamiento no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(TreatmentVoter::DELETE, $treatment);

        $this->treatmentRepository->remove($treatment);
        return ApiJsonResponse::success('Tratamiento eliminado exitosamente');
    }

    /**
     * Marca un tratamiento como completado.
     * - Vet / Admin: pueden completar tratamientos para cualquier mascota.
     * - Cliente: no tiene permisos para completar tratamientos
     */
    #[Route('/{treatmentId}/complete', name: 'complete', methods: ['PUT', 'PATCH'] )]
    public function CompleteTreatment(
        int $treatmentId
    ): JsonResponse
    {
        if (! $this->isGranted('ROLE_VET') && ! $this->isGranted('ROLE_ADMIN')) {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $treatment = $this->treatmentRepository->find($treatmentId);
        if (!$treatment instanceof Treatment) {
            return ApiJsonResponse::error('Tratamiento no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->treatmentRepository->completeTreatment($treatment);

        return ApiJsonResponse::success('Tratamiento actualizado exitosamente');
    }

    /**
     * Suspende un tratamiento activo.
     * Solo admin y veterinarios pueden suspender un tratamiento activo.
     *
     * @param int $treatmentId
     * @return JsonResponse
     */
    #[Route('/{treatmentId}/suspend', name: 'suspend', methods: ['PUT', 'PATCH'] )]
    public function SuspendTreatment(int $treatmentId): JsonResponse
    {
        if (! $this->isGranted('ROLE_VET') && ! $this->isGranted('ROLE_ADMIN')) {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $treatment = $this->treatmentRepository->find($treatmentId);
        if (!$treatment instanceof Treatment) {
            return ApiJsonResponse::error('Tratamiento no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->treatmentRepository->suspendTreatment($treatment);

        return ApiJsonResponse::success('Tratamiento suspendido exitosamente');
    }

}