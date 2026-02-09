<?php

namespace App\Controller\backoffice;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/sponsor')]
class SponsorController extends AbstractController
{
    #[Route('', name: 'app_back_sponsor_index', methods: ['GET'])]
    public function index(Request $request, SponsorRepository $sponsorRepository): Response
    {
        $query = $request->query->get('q');
        $sponsors = $sponsorRepository->searchSponsors($query);

        if ($request->query->get('ajax')) {
            return $this->render('backoffice/sponsor/_table_body.html.twig', [
                'sponsors' => $sponsors,
            ]);
        }

        return $this->render('backoffice/sponsor/index.html.twig', [
            'sponsors' => $sponsors,
        ]);
    }

    #[Route('/new', name: 'app_back_sponsor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsor = new Sponsor();

        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (method_exists($sponsor, 'setCreatorId') && $this->getUser()) {
                $sponsor->setCreatorId($this->getUser());
            }

            $entityManager->persist($sponsor);
            $entityManager->flush();

            $this->addFlash('success', 'Sponsor created successfully!');
            return $this->redirectToRoute('app_back_sponsor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/sponsor/new.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_sponsor_show', methods: ['GET'])]
    public function show(Sponsor $sponsor): Response
    {
        return $this->render('backoffice/sponsor/show.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_sponsor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Sponsor updated successfully!');
            return $this->redirectToRoute('app_back_sponsor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/sponsor/edit.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_sponsor_delete', methods: ['POST'])]
    public function delete(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsor);
            $entityManager->flush();
            $this->addFlash('success', 'Sponsor deleted successfully!');
        }

        return $this->redirectToRoute('app_back_sponsor_index', [], Response::HTTP_SEE_OTHER);
    }
}
