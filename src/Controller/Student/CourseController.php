<?php
namespace App\Controller\Student;

use App\Entity\Course;
use App\Entity\Enrollement;
use App\Service\PdfExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CourseController extends AbstractController
{
    #[Route('/student/course/{id}', name: 'student_course', requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $em): Response
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

        $enrol = $em->getRepository(Enrollement::class)->findOneBy(['course' => $course, 'student' => $student]);
        $status = $enrol ? $enrol->getStatus() : 'LOCKED';

        $reviewSections = [];
        if (method_exists($course, 'getSectionsToReview')) {
            $reviewSections = $course->getSectionsToReview() ?? [];
        } elseif (method_exists($course, 'getSections_to_review')) {
            $reviewSections = $course->getSections_to_review() ?? [];
        }

        // build chapter list with associated quiz (if any) and compute completion
        $chapterRepo = $em->getRepository(\App\Entity\Chapter::class);
        $chapters = $chapterRepo->findBy(['course' => $course], ['chapter_order' => 'ASC']);
        $chapterItems = [];
        $attemptRepo = $em->getRepository(\App\Entity\QuizAttempts::class);
        $allPassed = true;
        foreach ($chapters as $chapter) {
            $quiz = $em->getRepository(\App\Entity\Quiz::class)->findOneBy(['chapter' => $chapter]);
            $passed = false;
            if ($quiz) {
                $qb = $attemptRepo->createQueryBuilder('a')
                    ->select('count(a.id)')
                    ->where('a.quiz = :q')
                    ->andWhere('a.student = :s')
                    ->andWhere('a.score >= :min')
                    ->setParameter('q', $quiz)
                    ->setParameter('s', $student)
                    ->setParameter('min', $quiz->getPassingScore() ?? 0);
                $count = (int) $qb->getQuery()->getSingleScalarResult();
                $passed = $count > 0;
            } else {
                $allPassed = false;
            }

            if (!$passed) {
                $allPassed = false;
            }

            $chapterItems[] = ['chapter' => $chapter, 'quiz' => $quiz, 'passed' => $passed];
        }

        return $this->render('student/course.html.twig', [
            'course' => $course,
            'status' => $status,
            'enrolment' => $enrol,
            'reviewSections' => $reviewSections,
            'chapters' => $chapterItems,
            'all_chapter_quizzes_passed' => $allPassed,
        ]);
    }

    #[Route('/student/course/{id}/export-pdf', name: 'student_course_export_pdf')]
    public function exportPdf(int $id, EntityManagerInterface $em, PdfExportService $pdfService): Response
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

        // Verify student is enrolled
        $enrol = $em->getRepository(Enrollement::class)->findOneBy(['course' => $course, 'student' => $student]);
        if (!$enrol) {
            $this->addFlash('error', 'Vous devez être inscrit au cours pour le télécharger.');
            return $this->redirectToRoute('student_course', ['id' => $id]);
        }

        // Get chapters
        $chapterRepo = $em->getRepository(\App\Entity\Chapter::class);
        $chapters = $chapterRepo->findBy(['course' => $course], ['chapter_order' => 'ASC']);
        $chapterItems = [];
        foreach ($chapters as $chapter) {
            $quiz = $em->getRepository(\App\Entity\Quiz::class)->findOneBy(['chapter' => $chapter]);
            $chapterItems[] = ['chapter' => $chapter, 'quiz' => $quiz];
        }

        // Generate PDF
        $pdfContent = $pdfService->generateCoursePdf($course, $chapterItems);
        $filename = $pdfService->getFilename($course);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
