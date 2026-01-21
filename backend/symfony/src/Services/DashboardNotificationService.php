<?php

namespace App\Services;

use App\Dto\DashboardNotificationDto;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use App\Repository\TreatmentRepository;

class DashboardNotificationService
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private TreatmentRepository $treatmentRepository,
    ) {}

    /**
     * Obtiene las notificaciones del dashboard
     *
     * @return array Lista de notificaciones en formato de array
     */
    public function getDashboardNotifications(): array
    {
        // un array de DashboardNotificationDto
        $notifications = [];

        // Nueva cita del cliente sin confirmar por clínica
        $pendingAppointments = $this->appointmentRepository->findBy(
            ['status'=>AppointmentStatus::Requested],
            ['createdAt'=>'DESC'],
        );


        foreach ($pendingAppointments as $appointment) {

            $slotDate = $appointment->getAppointmentSlot()->getSlotDate();
            $slotTime = $appointment->getAppointmentSlot()->getSlotTime();

            $appointmentDateTime = (clone $slotDate)->setTime(
                (int) $slotTime->format('H'),
                (int) $slotTime->format('i'));

            $notification = new DashboardNotificationDto(
                'Confirmar cita',
                '[' . $appointmentDateTime->format('d/m H:i') . '] ' . $appointment->getPet()->getClient()->getFullName(),
                'high',
                $appointment->getId(),
                'cita',
                $appointmentDateTime
            );
            $notifications[] = $notification;
        }

        // Tratamiento próximo a vencer (15 días)
        $expiringSoonTreatments = $this->treatmentRepository->findTreatmentsExpiringSoon(15);

        foreach ($expiringSoonTreatments as $treatment) {
            $notification = new DashboardNotificationDto(
                'Tratamiento Fin',
                '['. $treatment->getEndDate()->format('d/m') . ' ] mascota ' . $treatment->getPet()->getName(),
                'medium',
                $treatment->getPet()->getId(),
                'tratamiento',
                $treatment->getEndDate()
            );
            $notifications[] = $notification;
        }

        // Ordenar: primero citas, luego tratamientos
        usort($notifications, function (
            DashboardNotificationDto $a,
            DashboardNotificationDto $b
        ) {
            // Prioridad por tipo
            $priority = [
                'cita' => 1,
                'tratamiento' => 2,
            ];

            $typeA = $priority[$a->getType()] ?? 99;
            $typeB = $priority[$b->getType()] ?? 99;

            // Orden por tipo
            if ($typeA !== $typeB) {
                return $typeA <=> $typeB;
            }

            // Misma categoría → orden específico
            $dateA = $a->getDateTime()->getTimestamp();
            $dateB = $b->getDateTime()->getTimestamp();

            // Citas: primero las más próximas en el tiempo
            if ($a->getType() === 'cita') {
                return $dateA <=> $dateB; // ASC
            }

            // Tratamientos: vencen antes primero
            if ($a->getType() === 'tratamiento') {
                return $dateA <=> $dateB; // ASC
            }

            return 0;
        });


        // Convertir a arrays asociativos
        return array_map(fn($notification) => $notification->toArray(), $notifications);
    }

}