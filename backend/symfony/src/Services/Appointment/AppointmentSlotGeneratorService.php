<?php

namespace App\Services\Appointment;

use App\Entity\AppointmentSlot;
use App\Repository\AppointmentSlotRepository;
use App\Repository\ClinicScheduleRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class AppointmentSlotGeneratorService
{

    public function __construct(
        private EntityManagerInterface    $entityManager,
        private ClinicScheduleRepository  $clinicScheduleRepository,
        private AppointmentSlotRepository $appointmentSlotRepository,
    ){}

    /**
     * Genera los slots disponibles para una fecha determinada.
     *
     * @param \DateTimeImmutable $date
     * @return void
     */
    public function generateSlotsForDate(\DateTimeImmutable $date): void
    {
        $dayOfWeek = $date->format('N');

        $schedules = $this->clinicScheduleRepository->findBy(['dayOfWeek' => $dayOfWeek, 'isActive' => true]);

        if (!$schedules) {
            return;
        }

        foreach ($schedules as $schedule)
        {
            $start = $this->combine($date, $schedule->getStartTime());
            $end = $this->combine($date, $schedule->getEndTime());
            $step = $schedule->getSlotDurationMinutes();

            while ($start < $end) {
                if (!$this->appointmentSlotRepository->existsForDateAndTime($date, $start)) {
                    $slot = new AppointmentSlot();
                    $slot->setSlotDate($date);
                    $slot->setSlotTime($start);
                    $slot->setDurationMinutes($step);
                    $slot->setIsAvailable(true);

                    $this->entityManager->persist($slot);
                }

                // Incrementa el tiempo de inicio por el paso definido
                $start = $start->modify('+'.$step.' minutes');
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Combina fecha + hora
     */
    private function combine(DateTimeImmutable $date, DateTimeInterface $time): DateTimeImmutable
    {
        return $date->setTime(
            (int) $time->format('H'),
            (int) $time->format('i')
        );
    }
}