<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ClinicSchedule;
use App\Repository\ClinicScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-clinic-schedule',
    description: 'Crea el horario semanal de la clínica (lunes a sábado) si no existe ninguno, sin depender de fixtures.')]
class ConfigureClinicScheduleCommand extends Command
{
    private const SCHEDULE = [
        1 => [['09:00', '14:00'], ['16:00', '20:00']],
        2 => [['09:00', '14:00'], ['16:00', '20:00']],
        3 => [['09:00', '14:00'], ['16:00', '20:00']],
        4 => [['09:00', '14:00'], ['16:00', '20:00']],
        5 => [['09:00', '14:00'], ['16:00', '20:00']],
        6 => [['09:00', '14:00']],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClinicScheduleRepository $clinicScheduleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->clinicScheduleRepository->count([]) > 0) {
            $io->note('Ya existe un horario de clínica configurado. No se ha creado nada.');
            return Command::SUCCESS;
        }

        foreach (self::SCHEDULE as $dayOfWeek => $ranges) {
            foreach ($ranges as [$start, $end]) {
                $schedule = new ClinicSchedule();
                $schedule->setDayOfWeek($dayOfWeek);
                $schedule->setStartTime(new \DateTimeImmutable($start));
                $schedule->setEndTime(new \DateTimeImmutable($end));
                $schedule->setSlotDurationMinutes(30);
                $schedule->setIsActive(true);

                $this->entityManager->persist($schedule);
            }
        }

        $this->entityManager->flush();

        $io->success('Horario de la clínica creado correctamente (lunes a sábado).');

        return Command::SUCCESS;
    }
}
