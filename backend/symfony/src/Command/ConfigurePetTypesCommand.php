<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PetType;
use App\Repository\PetTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:configure-pet-types',
    description: 'Crea el catálogo de tipos de mascota si no existe ninguno, sin depender de fixtures.')]
class ConfigurePetTypesCommand extends Command
{
    private const PET_TYPES = [
        ['name' => 'Gato', 'description' => 'Felis catus'],
        ['name' => 'Perro', 'description' => 'Canis lupus familiaris'],
        ['name' => 'Ave', 'description' => 'Aves'],
        ['name' => 'Reptil', 'description' => 'Reptilia'],
        ['name' => 'Roedor', 'description' => 'Rodentia'],
        ['name' => 'Anfibios', 'description' => 'Amphibia'],
        ['name' => 'Otros', 'description' => 'Otros domésticos'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PetTypeRepository $petTypeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->petTypeRepository->count([]) > 0) {
            $io->note('Ya existe un catálogo de tipos de mascota. No se ha creado nada.');
            return Command::SUCCESS;
        }

        foreach (self::PET_TYPES as $data) {
            $petType = new PetType();
            $petType->setName($data['name']);
            $petType->setDescription($data['description']);

            $this->entityManager->persist($petType);
        }

        $this->entityManager->flush();

        $io->success('Catálogo de tipos de mascota creado correctamente.');

        return Command::SUCCESS;
    }
}
