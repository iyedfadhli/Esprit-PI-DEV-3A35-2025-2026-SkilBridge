<?php

namespace App\Controller\backoffice;

use App\Entity\SponsorHackathon;
use App\Form\SponsorHackathonType;
use App\Repository\SponsorHackathonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/backoffice/sponsor-hackathon')]
class SponsorHackathonController extends AbstractController
{
    #[Route('/', name: 'app_back_sponsor_hackathon_index', methods: ['GET'])]
    public function index(Request $request, SponsorHackathonRepository $sponsorHackathonRepository): Response
    {
        $query = $request->query->get('q');
        $sponsor_hackathons = $sponsorHackathonRepository->searchSponsors($query);

        if ($request->query->get('ajax')) {
            return $this->render('backoffice/sponsor_hackathon/_table_body.html.twig', [
                'sponsor_hackathons' => $sponsor_hackathons,
            ]);
        }

        return $this->render('backoffice/sponsor_hackathon/index.html.twig', [
            'sponsor_hackathons' => $sponsor_hackathons,
        ]);
    }

    #[Route('/new', name: 'app_back_sponsor_hackathon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsorHackathon = new SponsorHackathon();
        $form = $this->createForm(SponsorHackathonType::class, $sponsorHackathon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sponsorHackathon);
            $entityManager->flush();

            $this->addFlash('success', 'Sponsor assigned to hackathon successfully!');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/sponsor_hackathon/new.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_back_sponsor_hackathon_show', methods: ['GET'])]
    public function show(SponsorHackathon $sponsorHackathon): Response
    {
        return $this->render('backoffice/sponsor_hackathon/show.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_sponsor_hackathon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SponsorHackathon $sponsorHackathon, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SponsorHackathonType::class, $sponsorHackathon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Sponsor assignment updated successfully!');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/sponsor_hackathon/edit.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_back_sponsor_hackathon_delete', methods: ['POST'])]
    public function delete(Request $request, SponsorHackathon $sponsorHackathon, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsorHackathon->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsorHackathon);
            $entityManager->flush();
            $this->addFlash('success', 'Sponsor removed from hackathon successfully!');
        }

        return $this->redirectToRoute('app_back_sponsor_hackathon_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_back_sponsor_hackathon_pdf', methods: ['GET'])]
    public function downloadPdf(SponsorHackathon $sponsorHackathon): Response
    {
        // 1. Configure DomPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($pdfOptions);

        // 2. Render HTML
        $html = $this->renderView('backoffice/sponsor_hackathon/pdf_contract.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 3. Return PDF Response
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="contract_' . $sponsorHackathon->getId() . '.pdf"',
        ]);
    }
}
