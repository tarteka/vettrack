<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Factory\AppointmentFactory;
use App\Factory\AppointmentTypeFactory;
use App\Factory\PetFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PetFixtures::class,
            AppointmentTypeFixtures::class,
            AppointmentSlotFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $mainVet = $this->getReference(UserFixtures::MAIN_VET_REFERENCE, User::class);
        $mainClient = $this->getReference(UserFixtures::MAIN_CLIENT_REFERENCE, User::class);
        $mainClientPets = PetFactory::findBy(['client' => $mainClient]);

        $statuses = [
            AppointmentStatus::Requested,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Completed,
        ];

        foreach ($mainClientPets as $index => $mainClientPet) {
            AppointmentFactory::createOne([
                'pet' => $mainClientPet,
                'veterinarian' => $mainVet,
                'status' => $statuses[$index % count($statuses)],
            ]);
        }

        $pets = PetFactory::all();
        foreach ($pets as $pet) {
            AppointmentFactory::createOne([
                'pet' => $pet,
                'veterinarian' => $mainVet,
            ]);
        }

        AppointmentFactory::createMany(40);
    }
}