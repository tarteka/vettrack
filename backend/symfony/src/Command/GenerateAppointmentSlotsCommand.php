<?php

declare(strict_types=1);

namespace App\Command;

use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\Appointment\AppointmentSlotGeneratorService;

#[AsCommand(
    name: 'app:appointments:generate-slots',
    description: 'Genera los slots de citas según el horario de la clínica. Usar --days=XX para definir cuántos días generar (por defecto 90).')]
class GenerateAppointmentSlotsCommand extends Command
{
    public function __construct(
        private readonly AppointmentSlotGeneratorService $appointmentSlotGeneratorService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            null,
            InputOption::VALUE_OPTIONAL,
            'Número de días para los cuales generar los slots',
            90
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int) $input->getOption('days');

        if ($days <= 0) {
            $output->writeln('<error>El número de días debe ser mayor que 0.</error>');
            return Command::FAILURE;
        }

        $today = new DateTimeImmutable('today');

        $output->write(
            sprintf('<info>Generando slots para los próximos %d días...</info>', $days)
        );

        for ($i = 0; $i < $days; $i++) {
            $date = $today->modify("+{$i} days");
            $this->appointmentSlotGeneratorService->generateSlotsForDate($date);
        }

        $output->writeln('<info>✔ Slots generados correctamente</info>');

        return Command::SUCCESS;
    }
}
