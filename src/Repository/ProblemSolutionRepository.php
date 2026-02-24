<?php

namespace App\Repository;

use App\Entity\ProblemSolution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProblemSolution>
 */
class ProblemSolutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProblemSolution::class);
    }

    public function findByActivity(\App\Entity\Activity $activity): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.activityId = :activity')
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getResult();
    }

    public function findUnsolvedByActivity(\App\Entity\Activity $activity): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.activityId = :activity')
            ->andWhere('p.groupSolution IS NULL')
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getResult();
    }
}
