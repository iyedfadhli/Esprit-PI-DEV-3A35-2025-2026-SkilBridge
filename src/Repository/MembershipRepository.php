<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Membership>
 */
class MembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Membership::class);
    }

    public function findAdminMembershipsByUser(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('IDENTITY(m.user_id) = :uid')
            ->andWhere('m.role IN (:roles)')
            ->setParameter('uid', $userId)
            ->setParameter('roles', ['admin', 'leader'])
            ->getQuery()
            ->getResult();
    }

    public function countMembersInGroup(\App\Entity\Group $group): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.group_id = :group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function existsByUserAndGroup(User $user, Group $group): bool
    {
        $count = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.user_id = :user')
            ->andWhere('m.group_id = :group')
            ->setParameter('user', $user)
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function deleteByUserAndGroup(User $user, Group $group): int
    {
        return $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\Membership m WHERE m.user_id = :user AND m.group_id = :group')
            ->setParameter('user', $user)
            ->setParameter('group', $group)
            ->execute();
    }
}
