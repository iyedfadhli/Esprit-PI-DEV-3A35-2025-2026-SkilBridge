<?php

namespace App\Controller\backoffice;

use App\Entity\Sponsor;
use App\Entity\User;
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

        // Attach currently "connected" user based on session (custom auth)
        $userId = $request->getSession()->get('user_id');
        if ($userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);
            if ($user && method_exists($sponsor, 'setCreatorId')) {
                $sponsor->setCreatorId($user);
            }
        }

        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/export/csv', name: 'app_back_sponsor_export_csv', methods: ['GET'])]
    public function exportCsv(SponsorRepository $sponsorRepository): Response
    {
        // Get all sponsors
        $sponsors = $sponsorRepository->findBy([], [], 99);

        // Create CSV content
        $csvData = [];
        
        // Add header row
        $csvData[] = [
            'ID',
            'Name',
            'Description',
            'Logo URL',
            'Website URL',
            'Created At',
            'Creator Email'
        ];

        // Add data rows
        foreach ($sponsors as $sponsor) {
            // Clean description - replace newlines with spaces for CSV
            $description = $sponsor->getDescription() ?? '';
            $description = str_replace(["\r\n", "\r", "\n"], ' ', $description);
            $description = trim($description);

            $csvData[] = [
                $sponsor->getId(),
                $sponsor->getName() ?? '',
                $description,
                $sponsor->getLogoUrl() ?? '',
                $sponsor->getWebsiteUrl() ?? '',
                $sponsor->getCreatedAt() ? $sponsor->getCreatedAt()->format('Y-m-d H:i:s') : '',
                $sponsor->getCreatorId() && method_exists($sponsor->getCreatorId(), 'getEmail') ? $sponsor->getCreatorId()->getEmail() : ''
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Create response
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="sponsors_' . date('Y-m-d_His') . '.csv"');
        
        // Add BOM for Excel compatibility and set content
        $response->setContent("\xEF\xBB\xBF" . $csvContent);

        return $response;
    }
}
