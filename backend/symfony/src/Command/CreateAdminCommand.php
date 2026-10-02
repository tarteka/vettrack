<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crea un usuario administrador de forma interactiva, sin depender de fixtures.')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Crear usuario administrador');

        $email = $io->ask('Email');
        $dni = $io->ask('DNI');
        $firstName = $io->ask('Nombre');
        $lastName = $io->ask('Apellidos');

        $passwordQuestion = new Question('Contraseña');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $io->askQuestion($passwordQuestion);

        $confirmQuestion = new Question('Repite la contraseña');
        $confirmQuestion->setHidden(true);
        $confirmQuestion->setHiddenFallback(false);
        $passwordConfirm = $io->askQuestion($confirmQuestion);

        if ($password !== $passwordConfirm) {
            $io->error('Las contraseñas no coinciden.');
            return Command::FAILURE;
        }

        if (strlen((string) $password) < 8) {
            $io->error('La contraseña debe tener al menos 8 caracteres.');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setDni($dni);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([UserRole::Admin->value]);
        $user->setIsActive(true);
        $user->setIsVerified(true);

        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error($violation->getPropertyPath() . ': ' . $violation->getMessage());
            }
            return Command::FAILURE;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Administrador "%s" creado correctamente.', $email));

        return Command::SUCCESS;
    }
}
