<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    //    /**
    //     * @return Course[] Returns an array of Course objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Récupère tous les cours actifs avec leur quiz prérequis en une seule requête.
     * Utilise un LEFT JOIN pour éviter les requêtes N+1.
     *
     * @return Course[]
     */
    public function findActiveCoursesWithPrerequisites(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.prerequisite_quiz', 'pq')
            ->addSelect('pq')
            ->leftJoin('pq.course', 'pc')
            ->addSelect('pc')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
