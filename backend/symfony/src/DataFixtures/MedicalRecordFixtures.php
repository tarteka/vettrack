<?php

namespace App\DataFixtures;

use App\Factory\MedicalRecordFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MedicalRecordFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        MedicalRecordFactory::createMany(25);
    }

    public function getDependencies(): array
    {
        return [
            PetFixtures::class,
            UserFixtures::class,
        ];
    }
}