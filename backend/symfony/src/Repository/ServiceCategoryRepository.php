<?php

namespace App\Repository;

use App\Entity\ServiceCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceCategory::class);
    }

    /**
     * Guarda una nueva categoría de servicio en la base de datos.
     *
     * @param ServiceCategory $newServiceCategory La entidad ServiceCategory a guardar.
     */
    public function save(ServiceCategory $newServiceCategory): void
    {
        $this->getEntityManager()->persist($newServiceCategory);
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina una categoría de servicio de la base de datos.
     *
     * @param ServiceCategory $serviceCategory La entidad ServiceCategory a eliminar.
     */
    public function remove(ServiceCategory $serviceCategory): void
    {
        $this->getEntityManager()->remove($serviceCategory);
        $this->getEntityManager()->flush();
    }
}