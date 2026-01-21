<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\PetFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $mainClient = $this->getReference(UserFixtures::MAIN_CLIENT_REFERENCE, User::class);

        PetFactory::createMany(6, [
            'client' => $mainClient,
        ]);

        PetFactory::createMany(30);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PetTypeFixtures::class,
        ];
    }
}