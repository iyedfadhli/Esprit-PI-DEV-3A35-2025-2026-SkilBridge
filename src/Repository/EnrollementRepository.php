<?php

namespace App\Repository;

use App\Entity\Enrollement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrollement>
 */
class EnrollementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollement::class);
    }

    //    /**
    //     * @return Enrollement[] Returns an array of Enrollement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Récupère tous les enrolments COMPLETED d'un étudiant avec les cours associés.
     * Optimisé avec JOIN pour éviter le lazy loading.
     *
     * @return Enrollement[]
     */
    public function findCompletedByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.course', 'c')
            ->addSelect('c')
            ->where('e.student = :studentId')
            ->andWhere('e.status = :status')
            ->setParameter('studentId', $studentId)
            ->setParameter('status', 'COMPLETED')
            ->orderBy('e.completed_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les IDs des cours où l'étudiant a un enrolment (tous statuts).
     * Permet de filtrer les cours déjà inscrits.
     *
     * @return int[]
     */
    public function findEnrolledCourseIdsByStudent(int $studentId): array
    {
        $results = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.course) AS courseId')
            ->where('e.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->getQuery()
            ->getScalarResult();

        return array_map(fn($r) => (int) $r['courseId'], $results);
    }
}
