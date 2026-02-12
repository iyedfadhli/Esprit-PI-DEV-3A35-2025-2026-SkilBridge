<?php

namespace App\Repository;

use App\Entity\Sponsor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sponsor>
 */
class SponsorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sponsor::class);
    }

    public function searchSponsors(?string $query = null): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($query) {
            $qb->andWhere('s.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
