<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Devuelve todas las facturas ordenadas por fecha descendente.
     * Según número de factura.
     *
     * @return Invoice[]
     */
    public function findAllOrderedByDateDesc() : array{
        return $this->createQueryBuilder('i')
            ->orderBy('i.invoiceNumber', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve todas las facturas de un usuario ordenadas por fecha descendente.
     *
     * @param User $user
     * @return Invoice[]
     */
    public function findByUserOrderedByDateDesc(User $user) : array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.invoiceNumber', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Elimina una factura de la base de datos.
     *
     * @param Invoice $invoice
     * @return void
     */
    public function remove(Invoice $invoice): void
    {
        $this->getEntityManager()->remove($invoice);
        $this->getEntityManager()->flush();
    }

    /**
     * Guarda una factura en la base de datos.
     *
     * @param Invoice $invoice
     * @return void
     */
    public function save(Invoice $invoice): void
    {
        $this->getEntityManager()->persist($invoice);
        $this->getEntityManager()->flush();
    }
}