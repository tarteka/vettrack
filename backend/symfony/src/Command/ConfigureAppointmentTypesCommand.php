<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AppointmentType;
use App\Repository\AppointmentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-appointment-types',
    description: 'Crea el catálogo de tipos de cita si no existe ninguno, sin depender de fixtures.')]
class ConfigureAppointmentTypesCommand extends Command
{
    private const APPOINTMENT_TYPES = [
        ['name' => 'Vacuna', 'description' => 'Inmunización de mascotas para prevenir enfermedades'],
        ['name' => 'Revisión', 'description' => 'Chequeo general de salud y bienestar'],
        ['name' => 'Consulta', 'description' => 'Consulta veterinaria por cualquier motivo'],
        ['name' => 'Cirugía', 'description' => 'Procedimiento quirúrgico programado o de emergencia'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AppointmentTypeRepository $appointmentTypeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->appointmentTypeRepository->count([]) > 0) {
            $io->note('Ya existe un catálogo de tipos de cita. No se ha creado nada.');
            return Command::SUCCESS;
        }

        foreach (self::APPOINTMENT_TYPES as $data) {
            $type = new AppointmentType();
            $type->setName($data['name']);
            $type->setDescription($data['description']);

            $this->entityManager->persist($type);
        }

        $this->entityManager->flush();

        $io->success('Catálogo de tipos de cita creado correctamente.');

        return Command::SUCCESS;
    }
}
