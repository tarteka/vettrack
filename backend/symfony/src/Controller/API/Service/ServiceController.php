<?php

namespace App\Controller\API\Service;

use App\Dto\Service\Request\ServiceRequestDto;
use App\Entity\Service;
use App\Entity\ServiceCategory;
use App\Entity\User;
use App\Mapper\ServiceMapper;
use App\Repository\ServiceCategoryRepository;
use App\Repository\ServiceRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/services', name: 'api_services_')]
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly ServiceCategoryRepository $serviceCategoryRepository,
        private readonly ServiceMapper $serviceMapper
    )
    {}

    /**
     * Devuelve todos los servicios.
     * Filtra por ?is_active=true|false o no.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'find_all', methods: ['GET'])]
    public function findAll(Request $request): JsonResponse
    {
        $user = $this->getUser();


        if (! $user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        // Obtiene el parámetro de filtro de mascotas activas si existe
        $isActive = $request->query->has('is_active')
            ? $request->query->getBoolean('is_active') : null;

        $criteria = [];
        if ($isActive !== null) {
            $criteria['isActive'] = $isActive;
        }

        $services = $this->serviceRepository->findBy($criteria);
        $servicesResponse = $this->serviceMapper->toResponseDtoCollection($services);
        return ApiJsonResponse::success($servicesResponse);
    }

    /**
     * Devuelve un servicio por su ID.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    #[Route('/{serviceId}', name: 'find_one', methods: ['GET'])]
    public function findOneById(int $serviceId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }
        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN'))) {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }
        $service = $this->serviceRepository->find($serviceId);
        if (!$service instanceof Service) {
            return ApiJsonResponse::error('Servicio no encontrado', Response::HTTP_NOT_FOUND);
        }
        $serviceResponse = $this->serviceMapper->toResponseDto($service);
        return ApiJsonResponse::success($serviceResponse);
    }

    /**
     * Crea un nuevo servicio.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param ServiceRequestDto $dto
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        #[MapRequestPayload] ServiceRequestDto $dto
    ): JsonResponse
    {
        $serviceCategory = $this->serviceCategoryRepository->find($dto->categoryId);
        if (!$serviceCategory instanceof ServiceCategory) {
            return ApiJsonResponse::error('Categoría de servicio no encontrada');
        }

        $newService = $this->serviceMapper->toEntity($dto, $serviceCategory);
        $this->serviceRepository->save($newService);

        return ApiJsonResponse::success('Servicio creado correctamente', Response::HTTP_CREATED);
    }

    /**
     * Actualiza un servicio existente.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $serviceId
     * @param ServiceRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{serviceId}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int $serviceId,
        #[MapRequestPayload] ServiceRequestDto $dto
    ): JsonResponse
    {
        $service = $this->serviceRepository->find($serviceId);
        if (!$service instanceof Service) {
            return ApiJsonResponse::error('Servicio no encontrado', Response::HTTP_NOT_FOUND);
        }

        $serviceCategory = $this->serviceCategoryRepository->find($dto->categoryId);
        if (!$serviceCategory instanceof ServiceCategory) {
            return ApiJsonResponse::error('Categoría de servicio no encontrada');
        }

        $this->serviceMapper->updateEntity($service, $serviceCategory, $dto);

        $this->serviceRepository->save($service);

        return ApiJsonResponse::success('Servicio actualizado correctamente');
    }

    /**
     * Elimina un servicio existente.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    #[Route('/{serviceId}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->find($serviceId);
        if (!$service instanceof Service) {
            return ApiJsonResponse::error('Servicio no encontrado', Response::HTTP_NOT_FOUND);
        }

        $this->serviceRepository->remove($service);

        return ApiJsonResponse::success('Servicio eliminado correctamente');
    }

    /**
     * Desactiva un servicio existente.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    #[Route('/{serviceId}/deactivate', name: 'deactivate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deactivate(int $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->find($serviceId);
        if (!$service instanceof Service) {
            return ApiJsonResponse::error('Servicio no encontrado', Response::HTTP_NOT_FOUND);
        }

        if (!$service->isActive()) {
            return ApiJsonResponse::error('El servicio ya está desactivado');
        }

        $service->setIsActive(false);
        $this->serviceRepository->save($service);

        return ApiJsonResponse::success('Servicio desactivado correctamente');
    }

    /**
     * Activa un servicio existente.
     * Solo administradores pueden acceder a este recurso.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    #[Route('/{serviceId}/activate', name: 'activate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function activate(int $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->find($serviceId);
        if (!$service instanceof Service) {
            return ApiJsonResponse::error('Servicio no encontrado', Response::HTTP_NOT_FOUND);
        }

        if ($service->isActive()) {
            return ApiJsonResponse::error('El servicio ya está activo');
        }

        $service->setIsActive(true);
        $this->serviceRepository->save($service);

        return ApiJsonResponse::success('Servicio activado correctamente');
    }
}