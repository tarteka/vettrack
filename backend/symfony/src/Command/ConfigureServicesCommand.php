<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Service;
use App\Repository\ServiceCategoryRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-services',
    description: 'Crea el catálogo de servicios de la clínica si no existe ninguno, sin depender de fixtures.')]
class ConfigureServicesCommand extends Command
{
    private const SERVICES = [
        ['name' => 'Consulta general', 'category' => 'Consultas Veterinarias', 'description' => 'Revisión general de salud, diagnóstico y orientación veterinaria.', 'price' => 35.00],
        ['name' => 'Vacunación antirrábica', 'category' => 'Vacunaciones', 'description' => 'Vacuna frente a la rabia, incluye cartilla sanitaria.', 'price' => 25.00],
        ['name' => 'Desparasitación interna y externa', 'category' => 'Consultas Veterinarias', 'description' => 'Tratamiento antiparasitario completo para perros y gatos.', 'price' => 18.00],
        ['name' => 'Limpieza dental', 'category' => 'Cirugías', 'description' => 'Limpieza bucodental bajo sedación con eliminación de sarro.', 'price' => 90.00],
        ['name' => 'Esterilización / castración', 'category' => 'Cirugías', 'description' => 'Intervención quirúrgica de esterilización, incluye postoperatorio.', 'price' => 150.00],
        ['name' => 'Identificación con microchip', 'category' => 'Consultas Veterinarias', 'description' => 'Implantación de microchip y registro en el censo oficial.', 'price' => 20.00],
        ['name' => 'Pienso premium 3kg', 'category' => 'Productos Alimenticios', 'description' => 'Alimento seco de alta calidad para perros o gatos adultos.', 'price' => 45.00],
        ['name' => 'Urgencia veterinaria 24h', 'category' => 'Servicios de Emergencia', 'description' => 'Atención veterinaria urgente fuera de horario habitual.', 'price' => 80.00],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceRepository $serviceRepository,
        private readonly ServiceCategoryRepository $serviceCategoryRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->serviceRepository->count([]) > 0) {
            $io->note('Ya existe un catálogo de servicios. No se ha creado nada.');
            return Command::SUCCESS;
        }

        foreach (self::SERVICES as $data) {
            $category = $this->serviceCategoryRepository->findOneBy(['name' => $data['category']]);

            if (!$category) {
                $io->error(sprintf(
                    'Categoría "%s" no encontrada. Ejecuta antes app:configure-service-categories.',
                    $data['category']
                ));
                return Command::FAILURE;
            }

            $service = new Service();
            $service->setName($data['name']);
            $service->setDescription($data['description']);
            $service->setCategory($category);
            $service->setUnitPrice($data['price']);
            $service->setTaxRate(21.00);
            $service->setIsActive(true);

            $this->entityManager->persist($service);
        }

        $this->entityManager->flush();

        $io->success('Catálogo de servicios creado correctamente.');

        return Command::SUCCESS;
    }
}
