<?php

namespace App\Controller\API\Service;

use App\Dto\Service\Request\ServiceCategoryRequestDto;
use App\Entity\ServiceCategory;
use App\Entity\User;
use App\Mapper\ServiceCategoryMapper;
use App\Repository\ServiceCategoryRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/service-categories', name: 'service_categories_')]
class ServiceCategoryController extends AbstractController
{
    public function __construct(
        private readonly ServiceCategoryRepository $serviceCategoryRepository,
        private readonly ServiceCategoryMapper $serviceCategoryMapper
    ){}

    /**
     * Devuelve todas las categorías de servicios.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'find_all', methods: ['GET'])]
    public function findAll() : JsonResponse
    {
        $user = $this->getUser();

        if (! $user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $serviceCategories = $this->serviceCategoryRepository->findAll();
        $serviceCategoriesResponse = $this->serviceCategoryMapper->toResponseDtoCollection($serviceCategories);

        return ApiJsonResponse::success($serviceCategoriesResponse);
    }

    /**
     * Devuelve una categoría de servicio por su ID.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     *
     * @param int $ServiceCategoryId
     * @return JsonResponse
     */
    #[Route('/{ServiceCategoryId}', name: 'find_one', methods: ['GET'])]
    public function findOneById(int $ServiceCategoryId) : JsonResponse
    {
        $user = $this->getUser();

        if (! $user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $serviceCategory = $this->serviceCategoryRepository->find($ServiceCategoryId);
        if (!$serviceCategory instanceof ServiceCategory) {
            return ApiJsonResponse::error('Categoría de servicio no encontrada', Response::HTTP_NOT_FOUND);
        }

        return ApiJsonResponse::success(
            $this->serviceCategoryMapper->toResponseDto($serviceCategory));
    }

    /**
     * Crea una nueva categoría de servicio.
     * Solo administradores pueden crear una categoría de servicio.
     *
     * @param ServiceCategoryRequestDto $dto
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newServiceCategory(
        #[MapRequestPayload] ServiceCategoryRequestDto $dto
    ): JsonResponse
    {
        $newServiceCategory = $this->serviceCategoryMapper->toEntity($dto);
        $this->serviceCategoryRepository->save($newServiceCategory);
        return ApiJsonResponse::success('Categoría de servicio creada exitosamente', Response::HTTP_CREATED);
    }

    /**
     * Actualiza una categoría de servicio existente.
     * Solo administradores pueden actualizar una categoría de servicio.
     *
     * @param int $serviceCategoryId
     * @param ServiceCategoryRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{serviceCategoryId}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int $serviceCategoryId,
        #[MapRequestPayload] ServiceCategoryRequestDto $dto
    ): JsonResponse
    {
        $serviceCategory = $this->serviceCategoryRepository->find($serviceCategoryId);
        if (!$serviceCategory instanceof ServiceCategory){
            return ApiJsonResponse::error('Categoría de servicio no encontrada', Response::HTTP_NOT_FOUND);
        }

        $this->serviceCategoryMapper->updateEntity($serviceCategory, $dto);
        $this->serviceCategoryRepository->save($serviceCategory);
        return ApiJsonResponse::success('Categoría de servicio actualizada exitosamente');
    }

    /**
     * Elimina una categoría de servicio por su ID.
     * Solo administradores pueden eliminar una categoría de servicio.
     *
     * @param int $ServiceCategoryId
     * @return JsonResponse
     */
    #[Route('/{ServiceCategoryId}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $ServiceCategoryId): JsonResponse
    {
        $serviceCategory = $this->serviceCategoryRepository->find($ServiceCategoryId);
        if (!$serviceCategory instanceof ServiceCategory){
            return ApiJsonResponse::error('Categoría de servicio no encontrada', Response::HTTP_NOT_FOUND);
        }

        $this->serviceCategoryRepository->remove($serviceCategory);
        return ApiJsonResponse::success('Categoría de servicio eliminada exitosamente');
    }

}

