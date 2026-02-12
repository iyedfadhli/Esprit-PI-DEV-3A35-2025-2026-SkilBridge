<?php
namespace App\Controller\backoffice;

use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\Commentaires;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
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

}
