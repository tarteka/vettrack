<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\Treatment;
use App\Enum\TreatmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TreatmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Treatment::class);
    }


    /**
     * Encuentra tratamientos que expirarán en los próximos 'int' días
     *
     * @param int $int Número de días para buscar tratamientos que expiran pronto
     * @return Treatment[] Lista de tratamientos que expiran pronto
     */
    public function findTreatmentsExpiringSoon(int $int) : array
    {
        $now = new \DateTime();
        $futureDate = (new \DateTime())->modify("+$int days");

        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.endDate BETWEEN :now AND :futureDate')
            ->andWhere('t.status = :status')
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('futureDate', $futureDate->format('Y-m-d'))
            ->setParameter('status', TreatmentStatus::Active)
            ->orderBy('t.endDate', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Guarda un nuevo tratamiento en la base de datos.
     *
     * @param Treatment $newTreatment La entidad Treatment a guardar.
     */
    public function save(Treatment $newTreatment): void
    {
        $this->getEntityManager()->persist($newTreatment);
        $this->getEntityManager()->flush();
    }

    /**
     * Elimina un tratamiento de la base de datos.
     *
     * @param Treatment $treatment La entidad Treatment a eliminar.
     */
    public function remove(Treatment $treatment): void
    {
        $this->getEntityManager()->remove($treatment);
        $this->getEntityManager()->flush();
    }

    /**
     * Marca un tratamiento como completado.
     *
     * @param Treatment $treatment La entidad Treatment a marcar como completada.
     */
    public function completeTreatment(Treatment $treatment): void
    {
        $treatment->setStatus(TreatmentStatus::Completed);
        $this->getEntityManager()->flush();
    }

    /**
     * Cuenta el número de tratamientos activos para una mascota específica.
     *
     * @param Pet $pet La mascota para la que se desea contar los tratamientos activos.
     * @return int
     */
    public function getActiveTreatmentsNumber(Pet $pet): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.pet = :pet')
            ->andWhere('t.status = :status')
            ->setParameter('pet', $pet)
            ->setParameter('status', TreatmentStatus::Active)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marca como completados los tratamientos activos cuya fecha de fin ha pasado.
     * Si endDate es NULL, no se marca como completado. (tratamientos sin fecha de fin)
     *
     * @return int Número de tratamientos actualizados
     */
    public function completeExpiredTreatments(): int
    {
        $today = new \DateTimeImmutable('today'); // Fecha actual sin hora

        $treatments = $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.endDate IS NOT NULL')
            ->andWhere('t.endDate <= :today')
            ->setParameter('status', TreatmentStatus::Active)
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        foreach ($treatments as $treatment) {
            // Marcamos como completado solo los que han vencido
            $treatment->setStatus(TreatmentStatus::Completed);
        }

        $this->getEntityManager()->flush();

        return count($treatments);
    }

    /*
     * Marca como suspendido un tratamiento activo.
     */
    public function suspendTreatment(Treatment $treatment): void
    {
        $treatment->setStatus(TreatmentStatus::Suspended);

        $this->getEntityManager()->flush();
    }
}