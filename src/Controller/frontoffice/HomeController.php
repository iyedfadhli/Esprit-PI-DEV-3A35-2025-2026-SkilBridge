<?php

namespace App\Controller\frontoffice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'home')]
   public function home(Request $request, EntityManagerInterface $em): Response
{
    
    return $this->render('frontoffice/home.html.twig');
}
}
