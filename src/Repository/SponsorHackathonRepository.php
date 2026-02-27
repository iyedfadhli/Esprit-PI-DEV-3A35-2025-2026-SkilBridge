<?php

namespace App\Repository;

use App\Entity\SponsorHackathon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SponsorHackathon>
 */
class SponsorHackathonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SponsorHackathon::class);
    }

    public function searchSponsors(?string $query = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.sponsor', 'sp')
            ->leftJoin('s.hackathon', 'h');

        if ($query) {
            $qb->andWhere('sp.name LIKE :query OR h.title LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSearchQuery(?string $query = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.sponsor', 'sp')
            ->leftJoin('s.hackathon', 'h');

        if ($query) {
            $qb->andWhere('sp.name LIKE :query OR h.title LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->orderBy('s.id', 'DESC')->getQuery();
    }
}