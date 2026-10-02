<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ServiceCategory;
use App\Repository\ServiceCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-service-categories',
    description: 'Crea el catálogo de categorías de servicio si no existe ninguno, sin depender de fixtures.')]
class ConfigureServiceCategoriesCommand extends Command
{
    private const SERVICE_CATEGORIES = [
        ['name' => 'Consultas Veterinarias', 'description' => 'Evaluaciones y diagnósticos de salud para mascotas.'],
        ['name' => 'Cirugías', 'description' => 'Procedimientos quirúrgicos para mascotas.'],
        ['name' => 'Vacunaciones', 'description' => 'Administración de vacunas preventivas.'],
        ['name' => 'Productos Alimenticios', 'description' => 'Alimentos y suplementos para diferentes tipos de mascotas.'],
        ['name' => 'Accesorios y Equipos', 'description' => 'Artículos para cuidado, entretenimiento y manejo de mascotas.'],
        ['name' => 'Servicios de Emergencia', 'description' => 'Atención veterinaria urgente y cuidados críticos.'],
        ['name' => 'Otros Servicios', 'description' => 'Categorías variadas de servicios y productos adicionales.'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceCategoryRepository $serviceCategoryRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->serviceCategoryRepository->count([]) > 0) {
            $io->note('Ya existe un catálogo de categorías de servicio. No se ha creado nada.');
            return Command::SUCCESS;
        }

        foreach (self::SERVICE_CATEGORIES as $data) {
            $category = new ServiceCategory();
            $category->setName($data['name']);
            $category->setDescription($data['description']);

            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();

        $io->success('Catálogo de categorías de servicio creado correctamente.');

        return Command::SUCCESS;
    }
}
