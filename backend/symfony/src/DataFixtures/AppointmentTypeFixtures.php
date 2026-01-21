<?php

namespace App\DataFixtures;

use App\Entity\AppointmentType;
use App\Factory\AppointmentTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppointmentTypeFixtures extends Fixture
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $types = [
            ['name' => 'Vacuna', 'description' => 'Inmunización de mascotas para prevenir enfermedades'],
            ['name' => 'Revisión', 'description' => 'Chequeo general de salud y bienestar'],
            ['name' => 'Consulta', 'description' => 'Consulta veterinaria por cualquier motivo'],
            ['name' => 'Cirugía', 'description' => 'Procedimiento quirúrgico programado o de emergencia'],
        ];

        AppointmentTypeFactory::createSequence($types);
    }
}