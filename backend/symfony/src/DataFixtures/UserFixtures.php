<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserFixtures extends Fixture
{
    public const VET_REFERENCE_PREFIX = 'vet_';
    public const MAIN_CLIENT_REFERENCE= 'main-client';
    const MAIN_VET_REFERENCE = 'main-vet';


    public function __construct(private readonly ParameterBagInterface $params)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        UserFactory::createOne([
            'email' => $this->params->get('ADMIN_EMAIL'),
            'password' => $this->params->get('ADMIN_PASSWORD'),
            'roles' => ['ROLE_ADMIN'],
            'firstName' => $this->params->get('ADMIN_FIRST_NAME'),
            'lastName' => $this->params->get('ADMIN_LAST_NAME'),
            'additionalNotes' => 'Es el administrador principal',
        ]);

        // Vet
        $mainVet = UserFactory::createOne([
            'email' => 'vet@vettrack.com',
            'password' => 'password',
            'roles' => ['ROLE_VET'],
            'firstName' => 'Veterinario',
            'lastName' => 'VetTrack',
            'additionalNotes' => 'Es el veterinario principal',
        ]);
        $this->addReference(self::MAIN_VET_REFERENCE, $mainVet->_real());

        UserFactory::createMany(2, [
            'roles' => ['ROLE_VET'],
        ]);

        // Client
        $mainClient = UserFactory::createOne([
            'email' => $this->params->get('CLIENT_EMAIL'),
            'password' => $this->params->get('CLIENT_PASSWORD'),
            'roles' => ['ROLE_CLIENT'],
            'firstName' => $this->params->get('CLIENT_FIRST_NAME'),
            'lastName' => $this->params->get('CLIENT_LAST_NAME'),
            'additionalNotes' => 'Es el cliente principal para pruebas',
        ]);
        $this->addReference(self::MAIN_CLIENT_REFERENCE, $mainClient->_real());

        UserFactory::createOne([
            'email' => 'client2@vettrack.com',
            'password' => 'password',
            'roles' => ['ROLE_CLIENT'],
            'firstName' => 'Darío',
            'lastName' => 'Porras Pérez',
        ]);

        // Clientes adicionales
        UserFactory::createMany(8, [
            'roles' => ['ROLE_CLIENT'],
        ]);
    }
}
