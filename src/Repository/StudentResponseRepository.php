<?php

namespace App\Repository;

use App\Entity\StudentResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentResponse>
 */
class StudentResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentResponse::class);
    }

    /**
     * Find all responses for a specific attempt
     */
    public function findByAttempt(int $attemptId): array
    {
        return $this->createQueryBuilder('sr')
            ->andWhere('sr.attempt = :attemptId')
            ->setParameter('attemptId', $attemptId)
            ->leftJoin('sr.question', 'q')
            ->addSelect('q')
            ->leftJoin('sr.selected_answer', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get statistics for a quiz
     */
    public function getQuizStatistics(int $quizId): array
    {
        return $this->createQueryBuilder('sr')
            ->select('q.id as question_id, q.content as question_content')
            ->addSelect('COUNT(sr.id) as total_responses')
            ->addSelect('SUM(CASE WHEN sr.is_correct = true THEN 1 ELSE 0 END) as correct_count')
            ->leftJoin('sr.question', 'q')
            ->leftJoin('sr.attempt', 'a')
            ->andWhere('a.quiz = :quizId')
            ->setParameter('quizId', $quizId)
            ->groupBy('q.id, q.content')
            ->getQuery()
            ->getResult();
    }
}
