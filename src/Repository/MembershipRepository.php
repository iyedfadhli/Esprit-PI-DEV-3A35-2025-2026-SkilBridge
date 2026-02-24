<?php

namespace App\Repository;

use App\Entity\Membership;
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
}
