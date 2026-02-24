<?php

namespace App\Controller\backoffice;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/quizzes')]
class QuizController extends AbstractController
{
    #[Route('', name: 'admin_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $courseFilter = $request->query->get('course', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Sorting
        $allowedSortFields = ['id', 'title', 'passingScore', 'maxAttempts'];
        $sort = $request->query->get('sort', 'id');
        $direction = strtoupper($request->query->get('direction', 'DESC'));
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        
        $queryBuilder = $quizRepository->createQueryBuilder('q')
            ->leftJoin('q.course', 'c')
            ->leftJoin('q.chapter', 'ch')
            ->leftJoin('q.supervisor', 's')
            ->addSelect('c', 'ch', 's');
        
        if ($search) {
            $queryBuilder->andWhere('q.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if ($courseFilter) {
            $queryBuilder->andWhere('c.id = :courseId')
                ->setParameter('courseId', $courseFilter);
        }

        $sortMapping = [
            'id' => 'q.id',
            'title' => 'q.title',
            'passingScore' => 'q.passing_score',
            'maxAttempts' => 'q.max_attempts',
        ];
        $queryBuilder->orderBy($sortMapping[$sort], $direction);
        
        $total = count($queryBuilder->getQuery()->getResult());
        $quizzes = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return $this->render('backoffice/quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'search' => $search,
            'courseFilter' => $courseFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quiz);
            $entityManager->flush();

            $this->addFlash('success', 'Quiz created successfully!');
            return $this->redirectToRoute('admin_quiz_index');
        }

        return $this->render('backoffice/quiz/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('backoffice/quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Quiz updated successfully!');
            return $this->redirectToRoute('admin_quiz_index');
        }

        return $this->render('backoffice/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quiz);
            $entityManager->flush();
            $this->addFlash('success', 'Quiz deleted successfully!');
        }

        return $this->redirectToRoute('admin_quiz_index');
    }
}
