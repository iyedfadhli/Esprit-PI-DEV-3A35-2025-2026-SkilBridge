<?php
namespace App\Controller\backoffice;

use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Group;
use App\Entity\Commentaires;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    #[Route('/admin/posts', name: 'admin_posts')]
    #[Route('/backoffice/posts', name: 'backoffice_posts')]
    #[Route('/admin/posts/index', name: 'admin_posts_index')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $sessionUserId = $request->getSession()->get('user_id');
        $currentUser = $sessionUserId ? $em->getRepository(User::class)->find($sessionUserId) : $this->getUser();
        if (!$currentUser || $currentUser->getMainRoleLabel() !== 'Admin') {
            return $this->redirectToRoute('groups_index');
        }

        $postRepo = $em->getRepository(Posts::class);
        $userRepo = $em->getRepository(User::class);
        $groupRepo = $em->getRepository(Group::class);
        $commentRepo = $em->getRepository(Commentaires::class);

        $stats = [
            'users' => $userRepo->count([]),
            'groups' => $groupRepo->count([]),
            'posts' => $postRepo->count([]),
            'comments' => $commentRepo->count([]),
        ];

        // Search
        $search = $request->query->get('search', '');

        // Sorting
        $allowedSortFields = ['id', 'titre', 'likesCounter', 'createdAt'];
        $sort = $request->query->get('sort', 'id');
        $direction = strtoupper($request->query->get('direction', 'DESC'));
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from(Posts::class, 'p')
            ->leftJoin('p.Author_id', 'a')
            ->addSelect('a');

        if ($search) {
            $qb->andWhere('p.titre LIKE :search OR p.description LIKE :search OR a.nom LIKE :search OR a.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $sortMapping = [
            'id' => 'p.id',
            'titre' => 'p.titre',
            'likesCounter' => 'p.likes_counter',
            'createdAt' => 'p.created_at',
        ];
        $qb->orderBy($sortMapping[$sort], $direction);

        $allResults = $qb->getQuery()->getResult();
        $total = count($allResults);
        $totalPages = (int) ceil($total / $limit);

        $posts = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('backoffice/posts/index.html.twig', [
            'stats' => $stats,
            'posts' => $posts,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
