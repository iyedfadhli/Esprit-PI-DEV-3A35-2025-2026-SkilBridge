<?php
namespace App\Controller\Student;

use App\Entity\Enrollement;
use App\Entity\Course;
use App\Entity\Quiz;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class StudentDashboardController extends AbstractController
{
    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $student = $this->getUser();
        if (!$student) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                $student = $em->getRepository(\App\Entity\User::class)->findOneBy([]);
            } else {
                throw $this->createAccessDeniedException('Full authentication is required to access this resource.');
            }
        }

        $enrolRepo = $em->getRepository(Enrollement::class);
        $courseRepo = $em->getRepository(Course::class);
        $quizRepo = $em->getRepository(Quiz::class);

        $courses = $courseRepo->findAll();

        $items = [];
        $attemptRepo = $em->getRepository(\App\Entity\QuizAttempts::class);
        foreach ($courses as $course) {
            $enrol = $enrolRepo->findOneBy(['course' => $course, 'student' => $student]);
            $status = $enrol ? $enrol->getStatus() : 'LOCKED';

            // determine whether the student passed all chapter quizzes
            $chapters = $em->getRepository(\App\Entity\Chapter::class)->findBy(['course' => $course]);
            $allPassed = true;
            foreach ($chapters as $chapter) {
                $quiz = $quizRepo->findOneBy(['chapter' => $chapter]);
                if (!$quiz) {
                    $allPassed = false;
                    break;
                }
                $qb = $attemptRepo->createQueryBuilder('a')
                    ->select('count(a.id)')
                    ->where('a.quiz = :q')
                    ->andWhere('a.student = :s')
                    ->andWhere('a.score >= :min')
                    ->setParameter('q', $quiz)
                    ->setParameter('s', $student)
                    ->setParameter('min', $quiz->getPassingScore() ?? 0);
                $count = (int) $qb->getQuery()->getSingleScalarResult();
                if ($count === 0) {
                    $allPassed = false;
                    break;
                }
            }

            $items[] = [
                'course' => $course,
                'status' => $status,
                'progress' => $enrol ? $enrol->getProgress() : 0,
                'score' => $enrol ? $enrol->getScore() : null,
                'enrolment' => $enrol,
                'all_chapter_quizzes_passed' => $allPassed,
            ];
        }

        return $this->render('student/dashboard.html.twig', [
            'courses' => $items,
        ]);
    }

    #[Route('/student/course/{id}/enrol', name: 'student_enrol_course', methods: ['POST'])]
    public function enrol(int $id, EntityManagerInterface $em): Response
    {
        $student = $this->getUser();
        if (!$student) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                $student = $em->getRepository(\App\Entity\User::class)->findOneBy([]);
            } else {
                throw $this->createAccessDeniedException('Full authentication is required to access this resource.');
            }
        }

        $course = $em->getRepository(Course::class)->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Cours introuvable');
        }

        $enrolRepo = $em->getRepository(Enrollement::class);
        $existing = $enrolRepo->findOneBy(['course' => $course, 'student' => $student]);
        if ($existing) {
            return $this->redirectToRoute('student_dashboard');
        }

        $enrol = new Enrollement();
        $enrol->setStudent($student);
        $enrol->setCourse($course);
        $enrol->setStatus('IN_PROGRESS');
        $enrol->setProgress(0);
        $enrol->setScore(null);
        $enrol->setCompletedAt(new \DateTime());

        $em->persist($enrol);
        $em->flush();

        return $this->redirectToRoute('student_course', ['id' => $course->getId()]);
    }
}
