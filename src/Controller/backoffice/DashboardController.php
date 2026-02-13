<?php
namespace App\Controller\backoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('backoffice/dashboard.html.twig');
    }
    

}
