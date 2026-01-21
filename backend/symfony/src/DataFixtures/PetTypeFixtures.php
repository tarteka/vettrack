<?php

namespace App\DataFixtures;

use App\Factory\PetTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PetTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $petTypes = [
            ['name' => 'Gato', 'description' => 'Felis catus'],
            ['name' => 'Perro', 'description' => 'Canis lupus familiaris'],
            ['name' => 'Ave', 'description' => 'Aves'],
            ['name' => 'Reptil', 'description' => 'Reptilia'],
            ['name' => 'Roedor', 'description' => 'Rodentia'],
            ['name' => 'Anfibios', 'description' => 'Amphibia'],
            ['name' => 'Otros', 'description' => 'Otros domésticos'],
        ];

        PetTypeFactory::createSequence($petTypes);
    }
}
