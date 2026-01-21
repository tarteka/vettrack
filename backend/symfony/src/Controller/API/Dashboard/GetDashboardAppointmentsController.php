<?php

namespace App\Controller\API\Dashboard;

use App\Dto\Appointment\Response\AppointmentDashboardDto;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard/appointments', name: 'api_dashboard_appointments', methods: ['GET'])]
class GetDashboardAppointmentsController extends AbstractController
{
    private AppointmentRepository $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * Maneja la solicitud para obtener las citas del dashboard
     * Devuelve una lista vacia si el usuario no tiene citas
     *
     * @return JsonResponse Respuesta JSON con la lista de citas
     */
    public function __invoke(): JsonResponse
    {
        // Obtener el usuario autenticado
        $user = $this->getUser();
        if (!$user instanceof User) {
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VETERINARIAN')) {
            // Obtener citas del día para admin y veterinarios
            $citas = $this->appointmentRepository->findTodayByUser($user);
        } else {
            // Obtener próximas citas para clientes
            $citas = $this->appointmentRepository->findUpcomingByUser($user);
        }

        $result = [];

        foreach ($citas as $cita){
            if (!$this->isGranted('VIEW', $cita))
            {
                continue;
            }
            $citaDto = new AppointmentDashboardDto($cita);
            $result[] = $citaDto->toArray();
        }

        return ApiJsonResponse::success($result);

    }
}