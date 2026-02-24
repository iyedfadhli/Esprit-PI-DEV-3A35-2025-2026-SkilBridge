<?php
namespace App\Controller\Student;

use App\Entity\QuizAttempts;
use App\Entity\StudentResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/student/quiz/{quizId}/attempt', name: 'student_quiz_attempt')]
    public function attempt(int $quizId, EntityManagerInterface $em): Response
    {
        $student = $this->getUser();
        if (!$student) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                $student = $em->getRepository(\App\Entity\User::class)->findOneBy([]);
            } else {
                throw $this->createAccessDeniedException('Full authentication is required to access this resource.');
            }
        }

        $quiz = $em->getRepository(\App\Entity\Quiz::class)->find($quizId);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz introuvable');
        }

        // Check max attempts
        $attemptRepo = $em->getRepository(\App\Entity\QuizAttempts::class);
        $existingAttempts = $attemptRepo->findBy(['student' => $student, 'quiz' => $quiz]);
        $maxAttempts = $quiz->getMaxAttempts();
        
        // If max_attempts is null or 0, allow unlimited attempts
        if ($maxAttempts !== null && $maxAttempts > 0 && count($existingAttempts) >= $maxAttempts) {
            $this->addFlash('error', 'Vous avez atteint le nombre maximum de tentatives pour ce quiz.');
            // Redirect back to course page instead of dashboard
            if ($quiz->getCourse()) {
                return $this->redirectToRoute('student_course', ['id' => $quiz->getCourse()->getId()]);
            }
            return $this->redirectToRoute('student_dashboard');
        }

        $nextNbr = count($existingAttempts) + 1;

        $attempt = new QuizAttempts();
        $attempt->setStudent($student);
        $attempt->setQuiz($quiz);
        $attempt->setAttemptNbr($nextNbr);
        $attempt->setScore(0.0);
        $attempt->setSubmittedAt(new \DateTimeImmutable());

        $em->persist($attempt);
        $em->flush();

        return $this->render('student/quiz_attempt.html.twig', [
            'attempt' => $attempt,
            'quiz' => $quiz,
        ]);
    }

    #[Route('/student/quiz/result/{attemptId}', name: 'student_quiz_result')]
    public function result(int $attemptId, EntityManagerInterface $em): Response
    {
        $attempt = $em->getRepository(QuizAttempts::class)->find($attemptId);
        if (!$attempt) {
            throw $this->createNotFoundException('Tentative introuvable');
        }

        // Get all student responses for this attempt
        $studentResponses = $em->getRepository(StudentResponse::class)
            ->findBy(['attempt' => $attempt], ['id' => 'ASC']);

        return $this->render('student/quiz_result.html.twig', [
            'attempt' => $attempt,
            'studentResponses' => $studentResponses,
        ]);
    }
}
