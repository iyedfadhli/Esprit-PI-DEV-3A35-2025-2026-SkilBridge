<?php
namespace App\Controller\backoffice;

use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\User;
use App\Entity\Commentaires;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupsController extends AbstractController
{
    #[Route('/admin/groups', name: 'admin_groups')]
    public function index(EntityManagerInterface $em): Response
    {
        $groupRepo = $em->getRepository(Group::class);
        $postRepo = $em->getRepository(Posts::class);
        $userRepo = $em->getRepository(User::class);
        $commentRepo = $em->getRepository(Commentaires::class);

        $groups = $groupRepo->findAll();

        $stats = [
            'users' => $userRepo->count([]),
            'groups' => $groupRepo->count([]),
            'posts' => $postRepo->count([]),
            'comments' => $commentRepo->count([]),
        ];

        return $this->render('backoffice/groups/index.html.twig', [
            'stats' => $stats,
            'groups' => $groups,
        ]);
    }
}
