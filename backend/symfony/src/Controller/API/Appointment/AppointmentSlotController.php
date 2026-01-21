<?php

namespace App\Controller\API\Appointment;

use App\Mapper\AppointmentSlotMapper;
use App\Repository\AppointmentSlotRepository;
use App\Response\ApiJsonResponse;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/appointment-slots', name: 'appointment_slots_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AppointmentSlotController extends AbstractController
{
    public function __construct(
        private readonly AppointmentSlotRepository $appointmentSlotRepository,
        private readonly AppointmentSlotMapper $appointmentSlotMapper,
    ){}

    /**
     * Devuelve las fechas disponibles para crear una nueva cita.
     * Las fechas serán entre 1 y 15 días a partir de hoy.
     * Por defecto se devuelven los últimos 7 días.
     * Solo autenticados pueden acceder a este recurso.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/available-dates', name: 'available_dates', methods: ['GET'])]
    public function getAvailableDates(Request $request): JsonResponse
    {
        // Entre 1 y 15 días máximo, por defecto 7.
        $days = max(1, min(
            $request->query->getInt('days', 7),
            15
        ));
        $from = new DateTimeImmutable('today');
        $to = $from->modify(sprintf('+%d days', $days));

        $dates = $this->appointmentSlotRepository->findAvailableDates($from, $to);

        return ApiJsonResponse::success($dates);
    }

    /**
     * Devuelve los horarios disponibles para una fecha
     * Solo autenticados pueden acceder a este recurso.
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/available-slots', name: 'available_slots', methods: ['GET'])]
    public function getAvailableSlotsByDate(Request $request): JsonResponse
    {
        $dateString = $request->query->get('date');

        if ($dateString === null) {
            return ApiJsonResponse::error(
                'El parámetro date es obligatorio (Y-m-d)'
            );
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        if ($date === false) {
            return ApiJsonResponse::error(
                'Formato de fecha inválido. Use Y-m-d'
            );
        }

        $slots = $this->appointmentSlotRepository->findAvailableSlotsByDate($date);

        $responseSlots = $this->appointmentSlotMapper->toAvailableSlotResponseList($slots);

        return ApiJsonResponse::success($responseSlots);
    }

}