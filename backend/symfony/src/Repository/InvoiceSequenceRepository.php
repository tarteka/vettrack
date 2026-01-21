<?php

namespace App\Repository;

use App\Entity\InvoiceSequence;
use App\Entity\Treatment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceSequenceRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceSequence::class);
    }

    public function save(InvoiceSequence $invoiceSequence): void
    {
        $this->getEntityManager()->persist($invoiceSequence);
        $this->getEntityManager()->flush();
    }

    /**
     * Devuelve la secuencia de facturación para un año específico con un bloqueo pesimista (FOR UPDATE).
     * @param int $year
     * @return InvoiceSequence|null
     */
    public function getForYearWithLock(int $year): ?InvoiceSequence
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE) // FOR UPDATE
            ->getOneOrNullResult();
    }
}