<?php
namespace App\Controller\backoffice;

use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Commentaires;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupsController extends AbstractController
{
    #[Route('/admin/groups', name: 'admin_groups')]
    #[Route('/backoffice/groups', name: 'backoffice_groups')]
    #[Route('/admin/groups/index', name: 'admin_groups_index')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $sessionUserId = $request->getSession()->get('user_id');
        $currentUser = $sessionUserId ? $em->getRepository(User::class)->find($sessionUserId) : $this->getUser();
        if (!$currentUser || $currentUser->getMainRoleLabel() !== 'Admin') {
            return $this->redirectToRoute('groups_index');
        }

        $groupRepo = $em->getRepository(Group::class);
        $postRepo = $em->getRepository(Posts::class);
        $userRepo = $em->getRepository(User::class);
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
        $allowedSortFields = ['id', 'name', 'type', 'level', 'creationDate'];
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
            ->select('g')
            ->from(Group::class, 'g');

        if ($search) {
            $qb->andWhere('g.name LIKE :search OR g.type LIKE :search OR g.level LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $sortMapping = [
            'id' => 'g.id',
            'name' => 'g.name',
            'type' => 'g.type',
            'level' => 'g.level',
            'creationDate' => 'g.creationDate',
        ];
        $qb->orderBy($sortMapping[$sort], $direction);

        $allResults = $qb->getQuery()->getResult();
        $total = count($allResults);
        $totalPages = (int) ceil($total / $limit);

        $groups = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('backoffice/groups/index.html.twig', [
            'stats' => $stats,
            'groups' => $groups,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
