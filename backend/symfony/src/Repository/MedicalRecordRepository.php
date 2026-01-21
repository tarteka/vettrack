<?php

namespace App\Repository;

use App\Entity\MedicalRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MedicalRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalRecord::class);
    }

    /**
     * Guarda un nuevo registro médico en la base de datos.
     *
     * @param MedicalRecord $newMedicalRecord La entidad MedicalRecord a guardar.
     */
    public function save(MedicalRecord $newMedicalRecord): void
    {
        $this->getEntityManager()->persist($newMedicalRecord);
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina un registro médico de la base de datos.
     *
     * @param MedicalRecord $medicalRecord La entidad MedicalRecord a eliminar.
     */
    public function remove(MedicalRecord $medicalRecord): void
    {
        $this->getEntityManager()->remove($medicalRecord);
        $this->getEntityManager()->flush();
    }

    /**
     * Encuentra todos los registros médicos asociados a una mascota específica.
     * Por defecto, los resultados se ordenan por fecha de registro en orden descendente.
     *
     * @param int $petId
     * @param string|null $order 'ASC' o 'DESC'
     * @return array <MedicalRecord> Los registros médicos asociados a la mascota.
     */
    public function findAllByPetId(int $petId, ?string $order='DESC'): array
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.pet = :petId')
            ->setParameter('petId', $petId)
            ->orderBy('mr.recordDate', $order)
            ->getQuery()
            ->getResult();
    }

    public function findLatestByPetId(int $petId): ?MedicalRecord
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.pet = :petId')
            ->setParameter('petId', $petId)
            ->orderBy('mr.recordDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestByClientId(int $clientId): ?MedicalRecord
    {
        return $this->createQueryBuilder('mr')
            ->andWhere('mr.user = :user')
            ->setParameter('user', $user)
            ->orderBy('mr.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}