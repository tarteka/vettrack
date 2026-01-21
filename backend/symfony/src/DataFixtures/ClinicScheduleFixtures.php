<?php

namespace App\DataFixtures;

use App\Entity\ClinicSchedule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClinicScheduleFixtures extends Fixture
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        // horario de lunes a sábado
        $schedule = [
            1 => [['09:00', '14:00'], ['16:00', '20:00']],
            2 => [['09:00', '14:00'], ['16:00', '20:00']],
            3 => [['09:00', '14:00'], ['16:00', '20:00']],
            4 => [['09:00', '14:00'], ['16:00', '20:00']],
            5 => [['09:00', '14:00'], ['16:00', '20:00']],
            6 => [['09:00', '14:00']],
        ];

        foreach ($schedule as $dayOfWeed => $ranges) {
            foreach ($ranges as [$start, $end]) {
                $slot = new ClinicSchedule();
                $slot->setDayOfWeek($dayOfWeed);
                $slot->setStartTime(new \DateTimeImmutable($start));
                $slot->setEndTime(new \DateTimeImmutable($end));
                $slot->setSlotDurationMinutes(30);
                $slot->setIsActive(true);

                $manager->persist($slot);
            }
        }

        $manager->flush();
    }
}
