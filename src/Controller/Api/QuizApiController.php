<?php
namespace App\Controller\Api;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\QuizAttempts;
use App\Entity\StudentResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizApiController extends AbstractController
{
    /**
     * @deprecated Utilisez POST /api/quizzes/{id}/start-attempt (QuizTimerController) à la place.
     * Ce endpoint est conservé pour compatibilité mais ne gère pas le timer.
     */
    #[Route('/api/quizzes/{quizId}/start', name: 'api_quiz_start', methods: ['POST'])]
    public function start(int $quizId, EntityManagerInterface $em): JsonResponse
    {
        $quiz = $em->getRepository(Quiz::class)->find($quizId);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz not found'], 404);
        }

        $questions = $em->getRepository(Question::class)->findBy(['quiz' => $quiz]);

        // shuffle and limit
        shuffle($questions);
        $limit = $quiz->getQuestionsPerAttempt() ?? count($questions);
        $questions = array_slice($questions, 0, $limit);

        $out = [];
        foreach ($questions as $q) {
            $answers = [];
            foreach ($q->getAnswers() as $a) {
                $answers[] = ['id' => $a->getId(), 'text' => $a->getContent()];
            }
            // shuffle answers order
            shuffle($answers);
            $out[] = ['id' => $q->getId(), 'text' => $q->getContent(), 'answers' => $answers];
        }

        return new JsonResponse([
            'questions'          => $out,
            'time_limit_seconds' => $quiz->getTimeLimitSeconds(),
        ]);
    }

    #[Route('/api/quizzes/{quizId}/submit', name: 'api_quiz_submit', methods: ['POST'])]
    public function submit(int $quizId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $quiz = $em->getRepository(Quiz::class)->find($quizId);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz not found'], 404);
        }

        $student = $this->getUser();
        if (!$student) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                $student = $em->getRepository(\App\Entity\User::class)->findOneBy([]);
            } else {
                return new JsonResponse(['error' => 'Authentication required'], 403);
            }
        }

        $data = json_decode($request->getContent(), true);
        $selectedAnswerIds = $data['answers'] ?? [];
        $questionIdToAnswerId = $data['responses'] ?? []; // New format: {questionId: answerId}
        
        if (!is_array($selectedAnswerIds) && !is_array($questionIdToAnswerId)) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        // If an attemptId was provided, update that attempt; otherwise find/create
        $attemptId = $data['attemptId'] ?? null;
        $attemptRepo = $em->getRepository(QuizAttempts::class);
        $attempt = null;
        if ($attemptId) {
            $attempt = $attemptRepo->find($attemptId);
            if ($attempt && ($attempt->getStudent()?->getId() !== $student->getId() || $attempt->getQuiz()?->getId() !== $quiz->getId())) {
                $attempt = null;
            }
        }

        if (!$attempt) {
            $latest = $attemptRepo->findBy(['quiz' => $quiz, 'student' => $student], ['id' => 'DESC'], 1);
            if (!empty($latest)) {
                $attempt = $latest[0];
            }
        }

        if (!$attempt) {
            $attempt = new QuizAttempts();
            $prev = $attemptRepo->count(['quiz' => $quiz, 'student' => $student]);
            $attempt->setAttemptNbr($prev + 1);
            $attempt->setScore(0);
            $attempt->setSubmittedAt(new \DateTimeImmutable());
            $attempt->setStudent($student);
            $attempt->setQuiz($quiz);
            $em->persist($attempt);
            $em->flush();
        }

        // ── Calculer les points totaux sur TOUTES les questions du quiz ──
        // Les questions non répondues comptent comme fausses (0 points).
        $allQuestions = $em->getRepository(Question::class)->findBy(['quiz' => $quiz]);
        $limit = $quiz->getQuestionsPerAttempt() ?? count($allQuestions);
        $questionsSlice = array_slice($allQuestions, 0, $limit);

        $totalPoints = 0;
        foreach ($questionsSlice as $q) {
            $totalPoints += $q->getPoint();
        }
        $totalQuestions = count($questionsSlice);

        $earnedPoints = 0;
        $correctCount = 0;

        // Handle new format with question-answer mapping
        if (!empty($questionIdToAnswerId)) {
            foreach ($questionIdToAnswerId as $questionId => $answerId) {
                $question = $em->getRepository(Question::class)->find($questionId);
                $answer = $em->getRepository(Answer::class)->find($answerId);
                
                if (!$question || !$answer) continue;
                
                $isCorrect = $answer->isCorrect();
                $pointsEarned = $isCorrect ? $question->getPoint() : 0;
                
                if ($isCorrect) {
                    $correctCount++;
                    $earnedPoints += $pointsEarned;
                }

                // Create StudentResponse
                $studentResponse = new StudentResponse();
                $studentResponse->setAttempt($attempt);
                $studentResponse->setQuestion($question);
                $studentResponse->setSelectedAnswer($answer);
                $studentResponse->setIsCorrect($isCorrect);
                $studentResponse->setPointsEarned($pointsEarned);
                $em->persist($studentResponse);
            }
        } else {
            // Legacy format: array of answer IDs
            foreach ($selectedAnswerIds as $answerId) {
                $answer = $em->getRepository(Answer::class)->find($answerId);
                if (!$answer) continue;
                
                $question = $answer->getQuestion();
                $isCorrect = $answer->isCorrect();
                $pointsEarned = $isCorrect ? $question->getPoint() : 0;
                
                if ($isCorrect) {
                    $correctCount++;
                    $earnedPoints += $pointsEarned;
                }

                // Create StudentResponse
                $studentResponse = new StudentResponse();
                $studentResponse->setAttempt($attempt);
                $studentResponse->setQuestion($question);
                $studentResponse->setSelectedAnswer($answer);
                $studentResponse->setIsCorrect($isCorrect);
                $studentResponse->setPointsEarned($pointsEarned);
                $em->persist($studentResponse);
            }
        }

        // Score calculé sur TOUTES les questions assignées (non répondues = 0 points)
        $percent = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        $attempt->setScore($percent);
        $attempt->setSubmittedAt(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse([
            'attemptId' => $attempt->getId(), 
            'score' => $percent,
            'correctCount' => $correctCount,
            'totalQuestions' => $totalQuestions,
            'earnedPoints' => $earnedPoints,
            'totalPoints' => $totalPoints,
            'passed' => $percent >= ($quiz->getPassingScore() ?? 70)
        ]);
    }
}
