<?php

namespace App\Repository;

use App\Entity\QuizAttempts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizAttempts>
 */
class QuizAttemptsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAttempts::class);
    }

    //    /**
    //     * @return QuizAttempts[] Returns an array of QuizAttempts objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Récupère les 3 derniers quiz réussis d'un étudiant (score >= passing_score).
     * Utilisé pour calculer le score moyen récent.
     *
     * @return QuizAttempts[]
     */
    public function findLastPassedAttempts(int $studentId, int $limit = 3): array
    {
        return $this->createQueryBuilder('qa')
            ->innerJoin('qa.quiz', 'q')
            ->addSelect('q')
            ->where('qa.student = :studentId')
            ->andWhere('qa.score >= q.passing_score')
            ->setParameter('studentId', $studentId)
            ->orderBy('qa.submitted_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
