<?php
namespace App\Controller\backoffice;

use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Group;
use App\Entity\Commentaires;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    #[Route('/admin/posts', name: 'admin_posts')]
    #[Route('/backoffice/posts', name: 'backoffice_posts')]
    #[Route('/admin/posts/index', name: 'admin_posts_index')]
    public function index(EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): Response
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

        $posts = $postRepo->findAll();

        $stats = [
            'users' => $userRepo->count([]),
            'groups' => $groupRepo->count([]),
            'posts' => $postRepo->count([]),
            'comments' => $commentRepo->count([]),
        ];

        return $this->render('backoffice/posts/index.html.twig', [
            'stats' => $stats,
            'posts' => $posts,
        ]);
    }
}
