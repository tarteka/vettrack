<?php

namespace App\DataFixtures;

use App\Entity\ServiceCategory;
use App\Factory\ServiceCategoryFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $serviceCategories = [
            [
                'name' => 'Consultas Veterinarias',
                'description' => 'Evaluaciones y diagnósticos de salud para mascotas.'
            ],
            [
                'name' => 'Cirugías',
                'description' => 'Procedimientos quirúrgicos para mascotas.'
            ],
            [
                'name' => 'Vacunaciones',
                'description' => 'Administración de vacunas preventivas.'
            ],
            [
                'name' => 'Productos Alimenticios',
                'description' => 'Alimentos y suplementos para diferentes tipos de mascotas.'
            ],
            [
                'name' => 'Accesorios y Equipos',
                'description' => 'Artículos para cuidado, entretenimiento y manejo de mascotas.'
            ],
            [
                'name' => 'Servicios de Emergencia',
                'description' => 'Atención veterinaria urgente y cuidados críticos.'
            ],
            [
                'name' => 'Otros Servicios',
                'description' => 'Categorías variadas de servicios y productos adicionales.'
            ],
        ];

        ServiceCategoryFactory::createSequence($serviceCategories);
    }
}
