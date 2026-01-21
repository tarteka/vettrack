<?php

namespace App\Services\Appointment;

use App\Entity\Appointment;
use App\Repository\ClinicSettingsRepository;
use App\Services\EmailService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class AppointmentEmailSenderService
{
    public function __construct(
        private EmailService $emailService,
        private ClinicSettingsRepository $clinicSettingsRepository,
    ){}


    /**
     * @throws TransportExceptionInterface
     */
    public function send(Appointment $appointment): void
    {
        // Solo enviamos email en estados notificables
        if (!in_array($appointment->getStatus()->name, ['Confirmed', 'Cancelled', 'Requested'], true)) {
            return;
        }

        $user = $appointment->getPet()->getClient();

        $clinic = $this->clinicSettingsRepository->find(1);

        if (!$user->getEmail())
        {
            throw new \DomainException('El cliente no tiene un email asociado');
        }

        // Asunto dinámico según estado
        $subject = match ($appointment->getStatus()->name) {
            'Confirmed' => 'Cita confirmada',
            'Cancelled' => 'Cita cancelada',
            'Requested' => 'Cita solicitada',
            default => 'Estado de tu cita',
        };

        $this->emailService->sendTemplate(
            to: $user->getEmail(),
            subject: $subject,
            templatePath: 'appointmentEmail.html.twig',
            context: [
                'user' => $user,
                'dia' => $appointment->getAppointmentSlot()->getSlotDate(),
                'hora' => $appointment->getAppointmentSlot()->getSlotTime(),
                'tipoConsulta' => $appointment->getAppointmentType()->getName(),
                'petName' => $appointment->getPet()->getName(),
                'status' => $appointment->getStatus()->name,
                'supportEmail' => $clinic->getEmail(),
                'supportPhone' => $clinic->getPhone(),
            ]
        );
    }
}