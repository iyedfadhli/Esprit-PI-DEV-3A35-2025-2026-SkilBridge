<?php
namespace App\Controller\backoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\Commentaires;
use App\Entity\User;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboard')]
    #[Route('/admin', name: 'admin_dashboard')]
    #[Route('/backoffice/dashboard', name: 'backoffice_dashboard')]
    public function dashboard(EntityManagerInterface $em, Request $request): Response
    {
        $sessionUserId = $request->getSession()->get('user_id');
        $currentUser = $sessionUserId ? $em->getRepository(User::class)->find($sessionUserId) : $this->getUser();
        if (!$currentUser || $currentUser->getMainRoleLabel() !== 'Admin') {
            return $this->redirectToRoute('groups_index');
        }
        $groupRepo = $em->getRepository(Group::class);
        $postRepo = $em->getRepository(Posts::class);
        $commentRepo = $em->getRepository(Commentaires::class);
        $userRepo = $em->getRepository(User::class);

        $groups = $groupRepo->findAll();
        $posts = $postRepo->findAll();

        $stats = [
            'users' => $userRepo->count([]),
            'groups' => $groupRepo->count([]),
            'posts' => $postRepo->count([]),
            'comments' => $commentRepo->count([]),
        ];

        return $this->render('backoffice/dashboard.html.twig', [
            'stats' => $stats,
            'groups' => $groups,
            'posts' => $posts,
        ]);
    }
#[Route('/user_dashboard', name: 'dashboard_user')]
public function user(EntityManagerInterface $em, Request $request): Response
{
    $search = $request->query->get('search');
    $sort = $request->query->get('sort');

    $userRepo = $em->getRepository(User::class);
    $qb = $userRepo->createQueryBuilder('u');

    if ($search) {
        $qb->where('u.nom LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    $validSortFields = ['id', 'nom', 'prenom', 'email'];
    if (in_array($sort, $validSortFields)) {
        $qb->orderBy('u.' . $sort, 'ASC');
    } else {
        $qb->orderBy('u.id', 'ASC'); 
    }

    $users = $qb->getQuery()->getResult();

    return $this->render('backoffice/user/user_dashboard.html.twig', [
        'users' => $users,
    ]);
}


}
