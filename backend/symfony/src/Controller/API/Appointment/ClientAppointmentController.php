<?php

namespace App\Controller\API\Appointment;

use App\Dto\Appointment\Request\CreateAppointmentClientRequestDto;
use App\Dto\Appointment\Request\UpdateAppointmentClientRequestDto;
use App\Entity\User;
use App\Mapper\AppointmentMapper;
use App\Repository\AppointmentRepository;
use App\Response\ApiJsonResponse;
use App\Services\Appointment\AppointmentEmailSenderService;
use App\Services\Appointment\AppointmentFromClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/client/appointments', name: 'client_appointments_')]
#[IsGranted('ROLE_CLIENT')]
class ClientAppointmentController extends AbstractController
{
    public function __construct(
        private readonly AppointmentFromClientService $appointmentService,
        private readonly AppointmentRepository $appointmentRepository,
        private readonly AppointmentMapper $appointmentMapper,
        private readonly AppointmentEmailSenderService $emailSenderService
    ){}

    /**
     * Crear una nueva cita por parte del cliente.
     * Solo clientes autenticados pueden acceder a este recurso.
     * Se crea con el estado de REQUESTED (solicitada).
     *
     * @param CreateAppointmentClientRequestDto $dto
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
       #[MapRequestPayload] CreateAppointmentClientRequestDto $dto,
    ) : JsonResponse
    {
        $client = $this->getUser();

        if (!$client instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointment = $this->appointmentService->create($dto, $client);

        try {
            $this->emailSenderService->send($appointment);
        } catch (TransportExceptionInterface $e) {
            // El email falla, pero la API sigue funcionando
        }

        return ApiJsonResponse::success(
            data: [
                'id' => $appointment->getId(),
                'status' => $appointment->getStatus()->value
                ],
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Cancela una cita.
     * Solo clientes autenticados pueden acceder a este recurso.
     * Eliminará si el estado es REQUESTED (solicitada).
     * Eliminará si es el propio cliente.
     *
     * @param int $appointmentId
     * @return JsonResponse
     */
    #[Route('/{appointmentId}/cancel', name: 'cancel', methods: ['PATCH', 'PUT'])]
    public function cancel(int $appointmentId): JsonResponse
    {
        $client = $this->getUser();

        if (!$client instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointment = $this->appointmentService
            ->cancel($appointmentId, $client);

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
     * Devuelve la lista de citas futuras del cliente autenticado.
     *
     * @return JsonResponse
     */
    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function upcoming() : JsonResponse
    {
        $client = $this->getUser();
        if (!$client instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointments = $this->appointmentRepository->findUpcomingByClient($client);

        $appointmentsDto = $this->appointmentMapper->toAppointmentsClientResponseDtoCollection($appointments);

        return ApiJsonResponse::success($appointmentsDto);
    }

    /**
     * Devuelve la lista de citas pasadas del cliente autenticado.
     *
     * @return JsonResponse
     */
    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(): JsonResponse
    {
        $client = $this->getUser();
        if (!$client instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointments = $this->appointmentRepository->findHistoryByClient($client);

        $appointmentsDto = $this->appointmentMapper->toAppointmentsClientResponseDtoCollection($appointments);

        return ApiJsonResponse::success($appointmentsDto);
    }

    /**
     * Actualiza una cita existente por parte del cliente.
     * Solo citas en estado de REQUESTED (solicitada) pueden ser actualizadas.
     * Solo clientes autenticados pueden acceder a este recurso.
     * @param int $appointmentId
     * @param UpdateAppointmentClientRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{appointmentId}', name: 'update', methods: ['PATCH'])]
    public function update(
        int $appointmentId,
        #[MapRequestPayload] UpdateAppointmentClientRequestDto $dto
    ): JsonResponse
    {
        $client = $this->getUser();

        if (!$client instanceof User){
            return ApiJsonResponse::error('Usuario no autenticado', Response::HTTP_UNAUTHORIZED);
        }

        $appointment =  $this->appointmentService->update($appointmentId, $dto, $client);

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