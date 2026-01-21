<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    /**
     * Guarda una nueva mascota en la base de datos.
     *
     * @param Pet $newPet La entidad Pet a guardar.
     */
    public function save(Pet $newPet): void
    {
        // Persistir la nueva mascota en la base de datos
        $this->getEntityManager()->persist($newPet);
        // Realizar el flush para guardar los cambios
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina una mascota de la base de datos.
     *
     * @param Pet $pet
     */
    public function remove(Pet $pet): void
    {
        $this->getEntityManager()->remove($pet);
        $this->getEntityManager()->flush();
    }

    /**
     * Actualiza el estado de todas las mascotas asociadas a un usuario.
     *
     * @param User $user El usuario cuyas mascotas serán actualizadas.
     * @param bool $status El nuevo estado a asignar a las mascotas.
     */
    public function setAllStatusByUser(User $user, bool $status): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->update(Pet::class, 'p')
            ->set('p.isActive', ':status')
            ->where('p.client = :user')
            ->setParameter('status', $status)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}