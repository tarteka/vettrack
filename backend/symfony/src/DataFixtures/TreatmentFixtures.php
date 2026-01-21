<?php

namespace App\DataFixtures;

use App\Factory\TreatmentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TreatmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        TreatmentFactory::createMany(75);
    }

    public function getDependencies(): array
    {
        return [
            MedicalRecordFixtures::class,
            PetFixtures::class,
            UserFixtures::class,
        ];
    }
}