<?php

namespace App\Controller\backoffice;

use App\Entity\Chapter;
use App\Form\ChapterType;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/chapters')]
class ChapterController extends AbstractController
{
    #[Route('', name: 'admin_chapter_index', methods: ['GET'])]
    public function index(ChapterRepository $chapterRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $courseFilter = $request->query->get('course', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Sorting
        $allowedSortFields = ['id', 'title', 'chapterOrder', 'status', 'minScore'];
        $sort = $request->query->get('sort', 'chapterOrder');
        $direction = strtoupper($request->query->get('direction', 'ASC'));
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'chapterOrder';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        $queryBuilder = $chapterRepository->createQueryBuilder('ch')
            ->leftJoin('ch.course', 'c')
            ->addSelect('c');
        
        if ($search) {
            $queryBuilder->andWhere('ch.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if ($courseFilter) {
            $queryBuilder->andWhere('c.id = :courseId')
                ->setParameter('courseId', $courseFilter);
        }

        $sortMapping = [
            'id' => 'ch.id',
            'title' => 'ch.title',
            'chapterOrder' => 'ch.chapter_order',
            'status' => 'ch.status',
            'minScore' => 'ch.min_score',
        ];
        $queryBuilder->orderBy($sortMapping[$sort], $direction);
        
        $total = count($queryBuilder->getQuery()->getResult());
        $chapters = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return $this->render('backoffice/chapter/index.html.twig', [
            'chapters' => $chapters,
            'search' => $search,
            'courseFilter' => $courseFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_chapter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chapter = new Chapter();
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chapter);
            $entityManager->flush();

            $this->addFlash('success', 'Chapter created successfully!');
            return $this->redirectToRoute('admin_chapter_index');
        }

        return $this->render('backoffice/chapter/new.html.twig', [
            'chapter' => $chapter,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_chapter_show', methods: ['GET'])]
    public function show(Chapter $chapter): Response
    {
        return $this->render('backoffice/chapter/show.html.twig', [
            'chapter' => $chapter,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_chapter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chapter $chapter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Chapter updated successfully!');
            return $this->redirectToRoute('admin_chapter_index');
        }

        return $this->render('backoffice/chapter/edit.html.twig', [
            'chapter' => $chapter,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_chapter_delete', methods: ['POST'])]
    public function delete(Request $request, Chapter $chapter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chapter->getId(), $request->request->get('_token'))) {
            $entityManager->remove($chapter);
            $entityManager->flush();
            $this->addFlash('success', 'Chapter deleted successfully!');
        }

        return $this->redirectToRoute('admin_chapter_index');
    }
}
