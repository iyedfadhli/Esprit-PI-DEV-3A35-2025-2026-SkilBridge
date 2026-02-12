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
    public function index(EntityManagerInterface $em): Response
    {
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
