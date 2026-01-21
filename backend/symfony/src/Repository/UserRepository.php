<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findActiveClientsWithPetCount(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u as user, COUNT(p.id) AS petCount')
            ->leftJoin(Pet::class, 'p', Join::WITH, 'p.client = u AND p.isActive = 1')
            ->where('JSON_CONTAINS(u.roles, :clientRole) = 1')
            ->andWhere('u.isActive = 1')
            ->groupBy('u.id')
            ->setParameter('clientRole', '["'.UserRole::Client->value.'"]')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     *
     * @param User $user La entidad User a guardar.
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Busca un veterinario aleatorio en la base de datos.
     * @return User|null
     */
    public function findRandomVet(): ?User
    {
        $vets = $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode('ROLE_VET'))
            ->getQuery()
            ->getResult();

        if (empty($vets)) {
            return null;
        }

        return $vets[array_rand($vets)];
    }

    /**
     * Busca un administrador o un veterinario aleatorio en la base de datos.
     * @return User|null
     */
    public function findRandomAdminOrVet(): ?User
    {
        $users = $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :admin) = 1')
            ->orWhere('JSON_CONTAINS(u.roles, :vet) = 1')
            ->setParameter('admin', json_encode('ROLE_ADMIN'))
            ->setParameter('vet', json_encode('ROLE_VET'))
            ->getQuery()
            ->getResult();

        if (empty($users)) {
            return null; // No hay admin ni vet
        }

        return $users[array_rand($users)];
    }
}
