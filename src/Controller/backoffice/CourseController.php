<?php

namespace App\Controller\backoffice;

use App\Entity\Course;
use App\Entity\User;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class CourseController extends AbstractController
{
    #[Route('', name: 'admin_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository, Request $request): Response
    {
        $searchValue = $request->query->get('search', '');
        $search = is_string($searchValue) ? $searchValue : '';
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Sorting
        $allowedSortFields = ['id', 'title', 'duration', 'validationScore'];
        $sortValue = $request->query->get('sort', 'id');
        $sort = is_string($sortValue) ? $sortValue : 'id';
        $directionValue = $request->query->get('direction', 'DESC');
        $direction = strtoupper(is_string($directionValue) ? $directionValue : 'DESC');
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        
        $queryBuilder = $courseRepository->createQueryBuilder('c')
            ->leftJoin('c.creator', 'u')
            ->addSelect('u');
        
        if ($search) {
            $queryBuilder->where('c.title LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Map sort field to query alias
        $sortMapping = [
            'id' => 'c.id',
            'title' => 'c.title',
            'duration' => 'c.duration',
            'validationScore' => 'c.validation_score',
        ];
        $queryBuilder->orderBy($sortMapping[$sort], $direction);
        
        $total = count($queryBuilder->getQuery()->getResult());
        $courses = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return $this->render('backoffice/course/index.html.twig', [
            'courses' => $courses,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creator = $form->get('creator')->getData();
            if ($creator instanceof User) {
                $course->assignCreator($creator);
            }
            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash('success', 'Course created successfully!');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('backoffice/course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        return $this->render('backoffice/course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creator = $form->get('creator')->getData();
            if ($creator instanceof User) {
                $course->assignCreator($creator);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Course updated successfully!');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('backoffice/course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $course->getId(), is_string($token) ? $token : null)) {
            $entityManager->remove($course);
            $entityManager->flush();
            $this->addFlash('success', 'Course deleted successfully!');
        }

        return $this->redirectToRoute('admin_course_index');
    }
}
