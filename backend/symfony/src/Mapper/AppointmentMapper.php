<?php

namespace App\Mapper;

use App\Dto\Appointment\Response\AppointmentsClientResponseDto;
use App\Dto\Appointment\Response\CalendarEventResponseDto;
use App\Entity\Appointment;
use DateTimeInterface;

class AppointmentMapper
{
    public function toCalendarEventResponseDto(
        Appointment $appointment
    ) : CalendarEventResponseDto
    {
        $slotDateTime = $appointment->getAppointmentSlot()->getDateTime();
        $endDate = (clone $slotDateTime)
            ->modify('+'.$appointment->getAppointmentSlot()->getDurationMinutes().' minutes');

        return new CalendarEventResponseDto(
            id: $appointment->getId(),
            title: sprintf(
                '%s - %s (%s)',
                strtoupper($appointment->getAppointmentType()->getName()),
                $appointment->getPet()->getName(),
                $appointment->getPet()->getClient()->getFullName()
            ),
            start: $slotDateTime->format(DateTimeInterface::ATOM),
            end: $endDate->format(DateTimeInterface::ATOM),
            extendedProps: [
                'appointmentSlotId' => $appointment->getAppointmentSlot()->getId(),
                'petName' => $appointment->getPet()->getName(),
                'clientName' => $appointment->getPet()->getClient()->getFullName(),
                'veterinarianName' => $appointment->getVeterinarian()?->getFullName(),
                'appointmentType' => $appointment->getAppointmentType()->getName(),
                'appointmentReason' => $appointment->getReason(),
                'status' => $appointment->getStatus()->value
            ]
        );
    }

    /**
     * Convierte una entidad Appointment a un DTO de respuesta para la API de clientes.
     *
     * @param Appointment $appointments
     * @return AppointmentsClientResponseDto
     */
    public function toAppointmentsClientResponseDto(Appointment $appointments) : AppointmentsClientResponseDto
    {
        return new AppointmentsClientResponseDto(
            id: $appointments->getId(),
            appointmentType: $appointments->getAppointmentType()->getName(),
            appointmentStatus: $appointments->getStatus()->value,
            petName: $appointments->getPet()->getName(),
            date: $appointments->getAppointmentSlot()->getSlotDate()->format('Y-m-d'),
            startTime: $appointments->getAppointmentSlot()->getSlotTime()->format('H:i'),
            veterinarian: $appointments->getVeterinarian()?->getFullName()
        );
    }

    /**
     * Convierte una colección (iterable) de Appointment a un array de DTOs de respuesta para la API de clientes.
     *
     * @param iterable<Appointment> $appointments
     * @return AppointmentsClientResponseDto[]
     */
    public function toAppointmentsClientResponseDtoCollection(iterable $appointments) : array
    {
        $dtos = [];
        foreach ($appointments as $appointment) {
            $dtos[] = $this->toAppointmentsClientResponseDto($appointment);
        }
        return $dtos;
    }
}