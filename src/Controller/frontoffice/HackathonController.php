<?php

namespace App\Controller\frontoffice;

use App\Repository\HackathonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hackathon')]
class HackathonController extends AbstractController
{
    #[Route('/', name: 'app_front_hackathon_index', methods: ['GET'])]
    public function index(HackathonRepository $hackathonRepository): Response
    {
        return $this->render('frontoffice/hackathon/index.html.twig', [
            'hackathons' => $hackathonRepository->findAll(),
        ]);
    }
}
