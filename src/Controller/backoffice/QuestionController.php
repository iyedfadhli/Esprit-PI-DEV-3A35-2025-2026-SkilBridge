<?php

namespace App\Controller\backoffice;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/questions')]
class QuestionController extends AbstractController
{
    #[Route('', name: 'admin_question_index', methods: ['GET'])]
    public function index(QuestionRepository $questionRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $quizFilter = $request->query->get('quiz', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Sorting
        $allowedSortFields = ['id', 'content', 'type', 'point'];
        $sort = $request->query->get('sort', 'id');
        $direction = strtoupper($request->query->get('direction', 'DESC'));
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        
        $queryBuilder = $questionRepository->createQueryBuilder('q')
            ->leftJoin('q.quiz', 'qz')
            ->leftJoin('qz.course', 'c')
            ->addSelect('qz', 'c');
        
        if ($search) {
            $queryBuilder->andWhere('q.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if ($quizFilter) {
            $queryBuilder->andWhere('qz.id = :quizId')
                ->setParameter('quizId', $quizFilter);
        }

        $sortMapping = [
            'id' => 'q.id',
            'content' => 'q.content',
            'type' => 'q.type',
            'point' => 'q.point',
        ];
        $queryBuilder->orderBy($sortMapping[$sort], $direction);
        
        $total = count($queryBuilder->getQuery()->getResult());
        $questions = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return $this->render('backoffice/question/index.html.twig', [
            'questions' => $questions,
            'search' => $search,
            'quizFilter' => $quizFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', 'Question created successfully!');
            return $this->redirectToRoute('admin_question_index');
        }

        return $this->render('backoffice/question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('backoffice/question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Question updated successfully!');
            return $this->redirectToRoute('admin_question_index');
        }

        return $this->render('backoffice/question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
            $this->addFlash('success', 'Question deleted successfully!');
        }

        return $this->redirectToRoute('admin_question_index');
    }
}
