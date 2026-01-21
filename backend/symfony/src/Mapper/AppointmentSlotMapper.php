<?php

namespace App\Mapper;

use App\Dto\Appointment\Response\AvailableDaySlotsResponseDto;
use App\Entity\AppointmentSlot;

class AppointmentSlotMapper
{
    /**
     * Mapea un AppointmentSlot a un DTO de respuesta
     *
     * @param AppointmentSlot $slot
     * @return AvailableDaySlotsResponseDto
     */
    public function toAvailableSlotResponse(AppointmentSlot $slot): AvailableDaySlotsResponseDto
    {
        $start = clone $slot->getSlotTime();
        $end = (clone $slot->getSlotTime())
            ->modify(sprintf('+%d minutes', $slot->getDurationMinutes()));

        return new AvailableDaySlotsResponseDto(
            $slot->getId(),
            $start->format('H:i'),
            $end->format('H:i')
        );
    }

    /**
     * Mapea un array de AppointmentSlots a un DTO de respuesta
     *
     * @param AppointmentSlot[] $slots
     * @return AvailableDaySlotsResponseDto[]
     */
    public function toAvailableSlotResponseList(array $slots): array
    {
        return array_map(
            fn (AppointmentSlot $slot) => $this->toAvailableSlotResponse($slot),
            $slots
        );
    }
}