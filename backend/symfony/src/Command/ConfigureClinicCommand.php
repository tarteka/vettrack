<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ClinicSettings;
use App\Repository\ClinicSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:configure-clinic',
    description: 'Crea o actualiza la fila única de configuración de la clínica a partir de las variables CLINIC_* del entorno, sin depender de fixtures.')]
class ConfigureClinicCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClinicSettingsRepository $clinicSettingsRepository,
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = $this->clinicSettingsRepository->find(1) ?? new ClinicSettings();

        $settings->setClinicName($this->params->get('CLINIC_NAME'));
        $settings->setCif($this->params->get('CLINIC_CIF'));
        $settings->setAddress($this->params->get('CLINIC_ADDRESS'));
        $settings->setPostalCode($this->params->get('CLINIC_POSTAL_CODE'));
        $settings->setCity($this->params->get('CLINIC_CITY'));
        $settings->setProvince($this->params->get('CLINIC_PROVINCE'));
        $settings->setCountry($this->params->get('CLINIC_COUNTRY'));
        $settings->setPhone($this->params->get('CLINIC_PHONE'));
        $settings->setEmail($this->params->get('CLINIC_EMAIL'));

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $io->success(sprintf('Configuración de la clínica "%s" guardada correctamente.', $settings->getClinicName()));

        return Command::SUCCESS;
    }
}
