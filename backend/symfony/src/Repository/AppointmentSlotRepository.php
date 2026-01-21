<?php

namespace App\Repository;

use App\Entity\AppointmentSlot;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

class AppointmentSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentSlot::class);
    }

    /**
     * Verifica si existe un slot disponible para una fecha y hora determinadas.
     *
     * @param DateTimeImmutable $date La fecha del horario.
     * @param DateTimeImmutable $time La hora del horario.
     * @return bool
     */
    public function existsForDateAndTime(
        DateTimeImmutable $date,
        DateTimeImmutable $time) : bool
    {
        return (bool) $this->createQueryBuilder('s')
            ->select('1')
            ->where('s.slotDate = :date')
            ->andWhere('s.slotTime = :time')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('time', $time->format('H:i:s'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve un array de fechas dentro del rango especificado,
     * que al menos tenga un slot libre.
     * Ejemplo: ["2026-01-05", "2026-01-06", "2026-01-08"]
     *
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @return array
     */
    public function findAvailableDates(DateTimeImmutable $from, DateTimeImmutable $to) : array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s.slotDate')
            ->where('s.slotDate BETWEEN :from AND :to')
            ->andWhere('s.isAvailable = true')
            ->orderBy('s.slotDate', 'ASC')
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Devuelve todos los slots libres de un día concreto, ordenado por horas.
     *
     * @param DateTimeImmutable $date
     * @return AppointmentSlot[]
     */
    public function findAvailableSlotsByDate(DateTimeImmutable $date) : array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.slotDate = :date')
            ->andWhere('s.isAvailable = true')
            ->setParameter('date', $date->format('Y-m-d'));

        $today = new DateTimeImmutable('today');

        if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
            $now = new DateTimeImmutable();
            $qb->andWhere('s.slotTime >= :now')
                ->setParameter('now', $now);
        }

        if ($date < $today) {
            return [];
        }

        return $qb
            ->orderBy('s.slotTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra un slot por su ID y lo bloquea para actualización.
     *
     * @param int $id
     * @return AppointmentSlot|null
     * @throws ORMException
     */
    public function findForUpdate(int $id) : ?AppointmentSlot
    {
        return $this->getEntityManager()->find(
            AppointmentSlot::class,
            $id,
            LockMode::PESSIMISTIC_WRITE
        );
    }
}