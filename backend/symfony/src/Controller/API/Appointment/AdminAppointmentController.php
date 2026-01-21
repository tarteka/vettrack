<?php

namespace App\Controller\API\Appointment;

use App\Dto\Appointment\Request\CalendarEventRequestDto;
use App\Dto\Appointment\Request\CreateAppointmentAdminRequestDto;
use App\Dto\Appointment\Request\UpdateAppointmentAdminRequestDto;
use App\Entity\Appointment;
use App\Entity\User;
use App\Mapper\AppointmentMapper;
use App\Repository\AppointmentRepository;
use App\Response\ApiJsonResponse;
use App\Services\Appointment\AppointmentService;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Services\Appointment\AppointmentEmailSenderService;

#[Route('/api/admin/appointments', name: 'admin_appointments_')]
#[IsGranted('ROLE_VET')]
class AdminAppointmentController extends AbstractController
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
        private readonly AppointmentMapper $appointmentMapper,
        private readonly AppointmentService $appointmentService,
        private readonly AppointmentEmailSenderService $emailSenderService
    ){}

    /**
     * Devuelve los eventos del calendario.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     *
     * @param CalendarEventRequestDto $dto
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/calendar', name: 'calendar')]
    public function calendarEvents(
        #[MapQueryString] CalendarEventRequestDto $dto
    ) : JsonResponse
    {
        $start = new DateTimeImmutable($dto->startDate);
        $end = new DateTimeImmutable($dto->endDate);

        // Obtener citas entre fechas
        $appointments = $this->appointmentRepository->findForCalendar($start, $end);

        // Mapear citas a eventos para el calendario
        $events = array_map(
            fn($appointment) => $this->appointmentMapper->toCalendarEventResponseDto($appointment),
            $appointments
        );

        return ApiJsonResponse::success($events);
    }

    /**
     * Devuelve una cita por su ID.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     * @param int $appointmentId
     * @return JsonResponse
     */
    #[Route('/{appointmentId}', name: 'get_one', methods: ['GET'])]
    public function getOneById(int $appointmentId) : JsonResponse
    {
        $appointment = $this->appointmentRepository->find($appointmentId);

        if (!$appointment instanceof Appointment) {
            return ApiJsonResponse::notFound('Cita no encontrada');
        }

        return ApiJsonResponse::success($this->appointmentMapper->toCalendarEventResponseDto($appointment));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateAppointmentAdminRequestDto $dto
    ): JsonResponse
    {
        $appointment = $this->appointmentService->create($dto, $this->getUser());

        try {
            $this->emailSenderService->send($appointment);
        } catch (TransportExceptionInterface $e) {
            // El email falla, pero la API sigue funcionando
        }

        return ApiJsonResponse::success(
            [
                'id'     => $appointment->getId(),
                'status' => $appointment->getStatus()->value,
            ],
            Response::HTTP_CREATED);
    }

    /**
     * Actualiza una cita.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     * @param int $appointmentId
     * @param UpdateAppointmentAdminRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{appointmentId}', name: 'update', methods: ['PATCH', 'PUT'])]
    public function update(
        int $appointmentId,
        #[MapRequestPayload] UpdateAppointmentAdminRequestDto $dto
    ): JsonResponse
    {
        $appointment = $this->appointmentService->update($appointmentId, $dto);

        try {
            $this->emailSenderService->send($appointment);
        } catch (TransportExceptionInterface $e) {
            // El email falla, pero la API sigue funcionando
        }

        return ApiJsonResponse::success(
            [
                'id'     => $appointment->getId(),
                'status' => $appointment->getStatus()->value,
            ]
        );
    }

    /**
     * Confirma una cita.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     * @param int $appointmentId
     * @return JsonResponse
     */
    #[Route('/{appointmentId}/confirm', name: 'confirm', methods: ['PATCH', 'PUT'])]
    public function confirm(int $appointmentId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointment = $this->appointmentService->confirmAppointment($appointmentId, $user);

        try {
            $this->emailSenderService->send($appointment);
        } catch (TransportExceptionInterface $e) {
            // El email falla, pero la API sigue funcionando
        }

        return ApiJsonResponse::success([
            'id'     => $appointment->getId(),
            'status' => $appointment->getStatus()->value,
        ]);
    }

    /**
     * Cancela una cita.
     * Solo administradores y veterinarios pueden acceder a este recurso.
     * @param int $appointmentId
     * @return JsonResponse
     */
    #[Route('/{appointmentId}/cancel', name: 'cancel', methods: ['PATCH', 'PUT'])]
    public function cancel(int $appointmentId): JsonResponse
    {
        $appointment = $this->appointmentService->cancelAppointment($appointmentId);

        try {
            $this->emailSenderService->send($appointment);
        } catch (TransportExceptionInterface $e) {
            // El email falla, pero la API sigue funcionando
        }

        return ApiJsonResponse::success([
            'id'     => $appointment->getId(),
            'status' => $appointment->getStatus()->value,
        ]);
    }
}