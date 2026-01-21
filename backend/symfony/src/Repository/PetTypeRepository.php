<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\PetType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PetTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PetType::class);
    }

    /**
     * Guarda un nuevo tipo de mascota en la base de datos.
     *
     * @param PetType $petType
     */
    public function save(PetType $petType): void
    {
        $this->getEntityManager()->persist($petType);
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina un tipo de mascota de la base de datos.
     *
     * @param PetType $petType
     */
    public function remove(PetType $petType): void
    {
        $this->getEntityManager()->remove($petType);
        $this->getEntityManager()->flush();
    }
}