<?php

namespace App\Repository;

use App\Entity\MemberActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MemberActivity>
 */
class MemberActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberActivity::class);
    }

    public function findByActivity(\App\Entity\Activity $activity): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id_activity = :activity')
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getResult();
    }

    public function findOneByActivityAndUser(\App\Entity\Activity $activity, \App\Entity\User $user): ?MemberActivity
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id_activity = :activity')
            ->andWhere('m.user_id = :user')
            ->setParameter('activity', $activity)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findListByUserAndActivity(\App\Entity\User $user, \App\Entity\Activity $activity): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user_id = :user')
            ->andWhere('m.id_activity = :activity')
            ->setParameter('user', $user)
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getResult();
    }
}
