<?php

namespace App\Controller\backoffice;

use App\Entity\Quiz;
use App\Entity\QuizAttempts;
use App\Entity\StudentResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/quiz-results')]
class QuizResultController extends AbstractController
{
    #[Route('', name: 'admin_quiz_results_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $quizRepo = $em->getRepository(Quiz::class);
        $attemptRepo = $em->getRepository(QuizAttempts::class);
        
        // Get all quizzes with attempt counts
        $quizzes = $quizRepo->findAll();
        
        $quizStats = [];
        foreach ($quizzes as $quiz) {
            $attempts = $attemptRepo->findBy(['quiz' => $quiz]);
            $totalAttempts = count($attempts);
            $passedCount = 0;
            $totalScore = 0;
            
            foreach ($attempts as $attempt) {
                $totalScore += $attempt->getScore();
                if ($attempt->getScore() >= ($quiz->getPassingScore() ?? 70)) {
                    $passedCount++;
                }
            }
            
            $quizStats[] = [
                'quiz' => $quiz,
                'totalAttempts' => $totalAttempts,
                'passedCount' => $passedCount,
                'averageScore' => $totalAttempts > 0 ? round($totalScore / $totalAttempts, 1) : 0,
                'passRate' => $totalAttempts > 0 ? round(($passedCount / $totalAttempts) * 100, 1) : 0,
            ];
        }
        
        return $this->render('backoffice/quiz_results/index.html.twig', [
            'quizStats' => $quizStats,
        ]);
    }

    #[Route('/{quizId}', name: 'admin_quiz_results_show')]
    public function show(int $quizId, Request $request, EntityManagerInterface $em): Response
    {
        $quiz = $em->getRepository(Quiz::class)->find($quizId);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz non trouvé');
        }

        $attemptRepo = $em->getRepository(QuizAttempts::class);
        
        // Search and filter
        $search = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', '');
        
        $qb = $attemptRepo->createQueryBuilder('a')
            ->leftJoin('a.student', 's')
            ->addSelect('s')
            ->where('a.quiz = :quiz')
            ->setParameter('quiz', $quiz)
            ->orderBy('a.submitted_at', 'DESC');
        
        if ($search) {
            $qb->andWhere('s.email LIKE :search OR s.nom LIKE :search OR s.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        if ($statusFilter === 'passed') {
            $qb->andWhere('a.score >= :passingScore')
               ->setParameter('passingScore', $quiz->getPassingScore() ?? 70);
        } elseif ($statusFilter === 'failed') {
            $qb->andWhere('a.score < :passingScore')
               ->setParameter('passingScore', $quiz->getPassingScore() ?? 70);
        }
        
        $attempts = $qb->getQuery()->getResult();
        
        // Calculate stats
        $totalAttempts = count($attempts);
        $passedCount = 0;
        $totalScore = 0;
        $studentIds = [];
        
        foreach ($attempts as $attempt) {
            $totalScore += $attempt->getScore();
            if ($attempt->getScore() >= ($quiz->getPassingScore() ?? 70)) {
                $passedCount++;
            }
            if ($attempt->getStudent()) {
                $studentIds[$attempt->getStudent()->getId()] = true;
            }
        }
        
        $stats = [
            'totalAttempts' => $totalAttempts,
            'uniqueStudents' => count($studentIds),
            'passedCount' => $passedCount,
            'failedCount' => $totalAttempts - $passedCount,
            'averageScore' => $totalAttempts > 0 ? round($totalScore / $totalAttempts, 1) : 0,
            'passRate' => $totalAttempts > 0 ? round(($passedCount / $totalAttempts) * 100, 1) : 0,
        ];
        
        return $this->render('backoffice/quiz_results/show.html.twig', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'stats' => $stats,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    #[Route('/attempt/{attemptId}', name: 'admin_quiz_results_attempt')]
    public function attemptDetail(int $attemptId, EntityManagerInterface $em): Response
    {
        $attempt = $em->getRepository(QuizAttempts::class)->find($attemptId);
        if (!$attempt) {
            throw $this->createNotFoundException('Tentative non trouvée');
        }
        
        $responses = $em->getRepository(StudentResponse::class)
            ->findBy(['attempt' => $attempt], ['id' => 'ASC']);
        
        // Calculate detailed stats
        $correctCount = 0;
        $totalPoints = 0;
        $earnedPoints = 0;
        
        foreach ($responses as $response) {
            if ($response->isCorrect()) {
                $correctCount++;
            }
            $totalPoints += $response->getQuestion()->getPoint();
            $earnedPoints += $response->getPointsEarned();
        }
        
        $stats = [
            'totalQuestions' => count($responses),
            'correctCount' => $correctCount,
            'incorrectCount' => count($responses) - $correctCount,
            'totalPoints' => $totalPoints,
            'earnedPoints' => $earnedPoints,
        ];
        
        return $this->render('backoffice/quiz_results/attempt.html.twig', [
            'attempt' => $attempt,
            'responses' => $responses,
            'stats' => $stats,
        ]);
    }
}
