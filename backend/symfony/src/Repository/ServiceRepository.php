<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class, );
    }

    /**
     * Guarda un nuevo servicio en la base de datos.
     *
     * @param Service $service La entidad Service a guardar.
     */
    public function save(Service $service): void
    {
        $this->getEntityManager()->persist($service);
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina un servicio de la base de datos.
     *
     * @param Service $service La entidad Service a eliminar.
     */
    public function remove(Service $service): void
    {
        $this->getEntityManager()->remove($service);
        $this->getEntityManager()->flush();
    }

}