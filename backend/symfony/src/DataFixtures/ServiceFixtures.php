<?php

namespace App\DataFixtures;

use App\Factory\ServiceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ServiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        ServiceFactory::createMany(20);
    }

    public function getDependencies(): array
    {
        return [
            ServiceCategoryFixtures::class,
        ];
    }
}