<?php

namespace App\DataFixtures;

use App\Services\Appointment\AppointmentSlotGeneratorService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppointmentSlotFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly AppointmentSlotGeneratorService $appointmentSlotGeneratorService
    ){}

    public function load(ObjectManager $manager): void
    {
        $today = new DateTimeImmutable('today');

        for ($i = 0; $i < 30; $i++) {
            $this->appointmentSlotGeneratorService->generateSlotsForDate(
                $today->modify("+{$i} days")
            );
        }
    }

    public function getDependencies(): array
    {
        return [
            ClinicScheduleFixtures::class
        ];
    }
}