<?php

namespace App\Controller\backoffice;

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/answers')]
class AnswerController extends AbstractController
{
    #[Route('', name: 'admin_answer_index', methods: ['GET'])]
    public function index(AnswerRepository $answerRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $questionFilter = $request->query->get('question', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Sorting
        $allowedSortFields = ['id', 'content', 'isCorrect'];
        $sort = $request->query->get('sort', 'id');
        $direction = strtoupper($request->query->get('direction', 'DESC'));
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        
        $queryBuilder = $answerRepository->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->leftJoin('q.quiz', 'qz')
            ->addSelect('q', 'qz');
        
        if ($search) {
            $queryBuilder->andWhere('a.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if ($questionFilter) {
            $queryBuilder->andWhere('q.id = :questionId')
                ->setParameter('questionId', $questionFilter);
        }

        $sortMapping = [
            'id' => 'a.id',
            'content' => 'a.content',
            'isCorrect' => 'a.is_correct',
        ];
        $queryBuilder->orderBy($sortMapping[$sort], $direction);
        
        $total = count($queryBuilder->getQuery()->getResult());
        $answers = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return $this->render('backoffice/answer/index.html.twig', [
            'answers' => $answers,
            'search' => $search,
            'questionFilter' => $questionFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_answer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($answer);
            $entityManager->flush();

            $this->addFlash('success', 'Answer created successfully!');
            return $this->redirectToRoute('admin_answer_index');
        }

        return $this->render('backoffice/answer/new.html.twig', [
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_answer_show', methods: ['GET'])]
    public function show(Answer $answer): Response
    {
        return $this->render('backoffice/answer/show.html.twig', [
            'answer' => $answer,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_answer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Answer updated successfully!');
            return $this->redirectToRoute('admin_answer_index');
        }

        return $this->render('backoffice/answer/edit.html.twig', [
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_answer_delete', methods: ['POST'])]
    public function delete(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $answer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($answer);
            $entityManager->flush();
            $this->addFlash('success', 'Answer deleted successfully!');
        }

        return $this->redirectToRoute('admin_answer_index');
    }
}
