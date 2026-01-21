<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * Encuentra citas basadas en el acceso del usuario
     * Rol de Admin y Veterinario pueden ver todas las citas
     *
     * @param User $user El usuario para el cual se buscan las citas
     * @return Appointment[] Lista de citas accesibles por el usuario
     */
    public function findByUserAccess(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('appointment')
            ->leftJoin('appointment.appointmentSlot', 'slot')->addSelect('slot')
            ->leftJoin('appointment.pet', 'pet')->addSelect('pet')
            ->leftJoin('pet.client', 'client')->addSelect('client')
            ->leftJoin('appointment.veterinarian', 'veterinarian')->addSelect('veterinarian')
            ->orderBy('slot.slotDate', 'DESC')
            ->addOrderBy('slot.slotTime', 'DESC');

        $isAdminOrVet = in_array('ROLE_ADMIN', $user->getRoles(), true) ||
            in_array('ROLE_VET', $user->getRoles(), true);

        if (!$isAdminOrVet) {
            $queryBuilder->andWhere('client.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findUpcomingByUser(User $user, int $limit = 5): array
    {
        $now = new \DateTimeImmutable();

        $queryBuilder = $this->createQueryBuilder('appointment')
            ->leftJoin('appointment.appointmentSlot', 'slot')->addSelect('slot')
            ->leftJoin('appointment.pet', 'pet')->addSelect('pet')
            ->leftJoin('pet.client', 'client')->addSelect('client')
            ->leftJoin('appointment.veterinarian', 'veterinarian')->addSelect('veterinarian')
            ->where('slot.slotDate >= :now')
            ->andWhere('appointment.status = :status')
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('status', AppointmentStatus::Confirmed->value)
            ->orderBy('slot.slotDate', 'ASC')
            ->addOrderBy('slot.slotTime', 'ASC')
            ->setMaxResults($limit);

        $isClient = !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !in_array('ROLE_VET', $user->getRoles(), true);

        if ($isClient) {
            $queryBuilder->andWhere('client.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findTodayByUser(User $user): array
    {
        $todayStart = (new \DateTimeImmutable())->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('appointment')
            ->leftJoin('appointment.appointmentSlot', 'slot')->addSelect('slot')
            ->leftJoin('appointment.pet', 'pet')->addSelect('pet')
            ->leftJoin('pet.client', 'client')->addSelect('client')
            ->leftJoin('appointment.veterinarian', 'veterinarian')->addSelect('veterinarian')
            ->where('slot.slotDate = :today')
            ->andWhere('appointment.status = :status')
            ->setParameter('today', $todayStart->format('Y-m-d'))
            ->setParameter('status', AppointmentStatus::Confirmed->value);

        $isClient = !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !in_array('ROLE_VET', $user->getRoles(), true);

        if ($isClient) {
            $qb->andWhere('client.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        $qb->orderBy('slot.slotTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Devuelve la fecha de la última cita completada para una mascota específica.
     *
     * @param Pet $pet La mascota para la cual se busca la última cita completada.
     * @return DateTimeInterface|null La fecha de la última cita completada o null si no existe.
     * @throws Exception
     */
    public function findLastCompletedByPet(Pet $pet): ?DateTimeInterface
    {
        $date = $this->createQueryBuilder('a')
            ->select('asl.slotDate')
            ->innerJoin('a.appointmentSlot', 'asl')
            ->where('a.pet = :pet')
            ->andWhere('a.status = :status')
            ->setParameter('pet', $pet)
            ->setParameter('status', AppointmentStatus::Completed->value)
            ->orderBy('asl.slotDate', Order::Descending->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
        ;

        return $date ? new \DateTimeImmutable($date) : null;
    }

    /**
     * Encuentra todas las citas confirmadas para el calendario basadas en el rango de fechas
     *
     * @param DateTimeInterface $start Fecha inicial
     * @param DateTimeInterface $end Fecha final
     * @return Appointment[]
     */
    public function findForCalendar(
        DateTimeInterface $start, DateTimeInterface $end
    ): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.appointmentSlot', 's')
            ->where('s.slotDate BETWEEN :start AND :end')
            ->andWhere('a.status = :status')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->setParameter('status', AppointmentStatus::Confirmed)
            ->getQuery()
            ->getResult();
    }

    /**
     * Realiza una consulta para actualizar una cita con LockMode::PESSIMISTIC_WRITE
     *
     * @param int $appointmentId
     * @return Appointment|null
     * @throws ORMException
     */
    public function findForUpdate(int $appointmentId) : ?Appointment
    {
        return $this->getEntityManager()
            ->find(
                Appointment::class,
                $appointmentId,
                LockMode::PESSIMISTIC_WRITE
            );
    }

    /**
     * Encuentra las citas confirmadas y solicitadas para el cliente actual.
     *
     * @param User $client El cliente para el cual se buscan las citas
     * @return Appointment[]
     */
    public function findUpcomingByClient(User $client): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('a')
            ->join('a.appointmentSlot', 's')
            ->join('a.pet', 'p')
            ->where('p.client = :client')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere('s.slotDate >= :today')
            ->setParameter('client', $client)
            ->setParameter('statuses', [
                AppointmentStatus::Requested,
                AppointmentStatus::Confirmed
            ])
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('s.slotDate', 'ASC')
            ->addOrderBy('s.slotTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Historial de citas para el cliente actual.
     * Encuentra las citas completadas y canceladas.
     */
    public function findHistoryByClient(User $client): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.appointmentSlot', 's')
            ->join('a.pet', 'p')
            ->where('p.client = :client')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('client', $client)
            ->setParameter('statuses', [
                AppointmentStatus::Completed,
                AppointmentStatus::Cancelled,
            ])
            ->orderBy('s.slotDate', 'DESC')
            ->addOrderBy('s.slotTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
