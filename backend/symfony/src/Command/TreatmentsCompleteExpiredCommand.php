<?php

namespace App\Command;

use App\Repository\TreatmentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:treatments:complete-expired',
    description: 'Marca como completados los tratamientos cuya fecha de fin ha pasado.',
)]
class TreatmentsCompleteExpiredCommand extends Command
{
    public function __construct(
        private readonly TreatmentRepository $treatmentRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->treatmentRepository->completeExpiredTreatments();

        $output->writeln(sprintf('>> %d tratamientos marcados como completados.', $count));

        return Command::SUCCESS;
    }
}
