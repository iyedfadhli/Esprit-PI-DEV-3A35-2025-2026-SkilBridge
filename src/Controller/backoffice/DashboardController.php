<?php
namespace App\Controller\backoffice;

use App\Entity\User;
use App\Entity\Student;
use App\Entity\Supervisor;
use App\Entity\Admin;
use App\Entity\Entreprise;

use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('backoffice/dashboard.html.twig');
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