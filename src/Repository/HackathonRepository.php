<?php

namespace App\Repository;

use App\Entity\Hackathon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hackathon>
 */
class HackathonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hackathon::class);
    }

    /**
     * @return list<Hackathon>
     */
    public function findLatest(int $limit = 50): array
    {
        return $this->createQueryBuilder('h')
            ->orderBy('h.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchHackathons(?string $query = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('h');

        if ($query) {
            $qb->andWhere('h.title LIKE :query OR h.theme LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($status) {
            $qb->andWhere('h.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->orderBy('h.created_at', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();
    }
}
