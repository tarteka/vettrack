<?php

namespace App\Controller\API\Appointment;

use App\Repository\AppointmentTypeRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/appointment-types', name: 'appointment_types_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AppointmentTypeController extends AbstractController
{
    public function __construct(
        private readonly AppointmentTypeRepository $appointmentTypeRepository,
    ) {}

    /**
     * Obtener todos los tipos de citas veterinarias.
     * Solo usuarios autenticados pueden acceder a este recurso.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'getAll', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $types = $this->appointmentTypeRepository->findAll();

        $response = array_map(
            static fn ($type) => [
                'id'   => $type->getId(),
                'name' => $type->getName(),
            ],
            $types
        );

        return ApiJsonResponse::success($response);
    }
}
