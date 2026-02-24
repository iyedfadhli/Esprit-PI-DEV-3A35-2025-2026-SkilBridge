<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function findByChallenge(\App\Entity\Challenge $challenge): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idChallenge = :challenge')
            ->setParameter('challenge', $challenge)
            ->getQuery()
            ->getResult();
    }

    public function findInProgressByChallengeAndGroup(\App\Entity\Challenge $challenge, \App\Entity\Group $group): ?Activity
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idChallenge = :challenge')
            ->andWhere('a.group_id = :group')
            ->andWhere('a.status = :status')
            ->setParameter('challenge', $challenge)
            ->setParameter('group', $group)
            ->setParameter('status', 'in_progress')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByChallengeAndGroup(\App\Entity\Challenge $challenge, \App\Entity\Group $group): ?Activity
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idChallenge = :challenge')
            ->andWhere('a.group_id = :group')
            ->setParameter('challenge', $challenge)
            ->setParameter('group', $group)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByChallengeAndGroup(\App\Entity\Challenge $challenge, \App\Entity\Group $group): bool
    {
        $count = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.idChallenge = :challenge')
            ->andWhere('a.group_id = :group')
            ->setParameter('challenge', $challenge)
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();
        return $count > 0;
    }
    public function findByUserMemberships(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->join('App\Entity\Membership', 'm', 'WITH', 'm.group_id = a.group_id')
            ->andWhere('IDENTITY(m.user_id) = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getResult();
    }

    public function hasAnyMemberInProgressConflict(\App\Entity\Group $candidateGroup): bool
    {
        $count = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('App\Entity\Membership', 'm', 'WITH', 'm.group_id = a.group_id')
            ->join('App\Entity\Membership', 'mg', 'WITH', 'IDENTITY(mg.user_id) = IDENTITY(m.user_id) AND mg.group_id = :candidate')
            ->andWhere('a.status = :status')
            ->setParameter('candidate', $candidateGroup)
            ->setParameter('status', 'in_progress')
            ->getQuery()
            ->getSingleScalarResult();
        return $count > 0;
    }
}
