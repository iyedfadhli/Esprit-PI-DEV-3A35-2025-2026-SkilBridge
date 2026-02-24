<?php

namespace App\Controller\backoffice;

use App\Entity\SponsorHackathon;
use App\Form\SponsorHackathonType;
use App\Service\EmailService;
use App\Repository\HackathonRepository;
use App\Repository\SponsorHackathonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

#[Route('/backoffice/sponsor-hackathon')]
class SponsorHackathonController extends AbstractController
{
    #[Route('/', name: 'app_back_sponsor_hackathon_index', methods: ['GET'])]
    public function index(Request $request, SponsorHackathonRepository $sponsorHackathonRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('q');
        $searchQuery = $sponsorHackathonRepository->getSearchQuery($query);
        $pagination = $paginator->paginate(
            $searchQuery,
            $request->query->getInt('page', 1),
            8
        );

        if ($request->query->get('ajax')) {
            return $this->render('backoffice/sponsor_hackathon/_table_body.html.twig', [
                'sponsor_hackathons' => $pagination->getItems(),
                'pagination' => $pagination,
            ]);
        }

        return $this->render('backoffice/sponsor_hackathon/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/calendar', name: 'app_back_sponsor_hackathon_calendar', methods: ['GET'])]
    public function calendar(HackathonRepository $hackathonRepository): Response
    {
        $hackathons = $hackathonRepository->findBy([], ['start_at' => 'ASC']);
        $events = [];
        foreach ($hackathons as $h) {
            if ($h->getStartAt() && $h->getEndAt()) {
                $events[] = [
                    'id' => $h->getId(),
                    'title' => $h->getTitle(),
                    'start' => $h->getStartAt()->format('c'),
                    'end' => $h->getEndAt()->format('c'),
                    'extendedProps' => [
                        'theme' => $h->getTheme(),
                        'location' => $h->getLocation() ?? '',
                        'url' => $this->generateUrl('app_back_hackathon_show', ['id' => $h->getId()]),
                    ],
                ];
            }
        }
        return $this->render('backoffice/sponsor_hackathon/calendar.html.twig', [
            'events' => $events,
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

            $amount = $sponsorHackathon->getContributionValue() ?? 0;
            if ($amount > 0) {
                Stripe::setApiKey($this->getParameter('stripe_secret_key'));
                $session = StripeSession::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => strtolower($this->getParameter('stripe_currency')),
                                'product_data' => [
                                    'name' => 'Sponsorship: ' . $sponsorHackathon->getContributionType(),
                                    'description' => $sponsorHackathon->getSponsor()->getName() . ' – ' . $sponsorHackathon->getHackathon()->getTitle(),
                                ],
                                'unit_amount' => (int) round($amount * 100),
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => $this->generateUrl('app_back_sponsor_hackathon_success', ['id' => $sponsorHackathon->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $this->generateUrl('app_back_sponsor_hackathon_cancel', ['id' => $sponsorHackathon->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'metadata' => ['sponsor_hackathon_id' => (string) $sponsorHackathon->getId()],
                ]);
                return $this->redirect($session->url, 303);
            }

            $this->addFlash('success', 'Sponsor assigned to hackathon successfully!');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/sponsor_hackathon/new.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/success/{id}', name: 'app_back_sponsor_hackathon_success', methods: ['GET'])]
    public function success(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsorHackathon = $entityManager->getRepository(SponsorHackathon::class)->find($id);
        if (!$sponsorHackathon) {
            $this->addFlash('error', 'Assignment not found.');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index');
        }

        $sessionId = $request->query->get('session_id');
        if ($sessionId) {
            try {
                Stripe::setApiKey($this->getParameter('stripe_secret_key'));
                $session = StripeSession::retrieve($sessionId);
                if ($session->payment_status !== 'paid' || (string) $sponsorHackathon->getId() !== ($session->metadata['sponsor_hackathon_id'] ?? null)) {
                    $this->addFlash('error', 'Invalid or unpaid session.');
                    return $this->redirectToRoute('app_back_sponsor_hackathon_index');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Could not verify payment.');
                return $this->redirectToRoute('app_back_sponsor_hackathon_index');
            }
        }

        return $this->render('backoffice/sponsor_hackathon/success_sign.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_back_sponsor_hackathon_cancel', methods: ['GET'])]
    public function cancel(int $id, EntityManagerInterface $entityManager): Response
    {
        $sponsorHackathon = $entityManager->getRepository(SponsorHackathon::class)->find($id);
        if ($sponsorHackathon) {
            $entityManager->remove($sponsorHackathon);
            $entityManager->flush();
        }
        $this->addFlash('warning', 'Payment was cancelled. The assignment was not saved.');
        return $this->redirectToRoute('app_back_sponsor_hackathon_index');
    }

    #[Route('/{id}/pdf-signed', name: 'app_back_sponsor_hackathon_pdf_signed', methods: ['POST'])]
    public function downloadPdfSigned(int $id, Request $request, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        if (!$this->isCsrfTokenValid('pdf_signed' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid request.');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index');
        }
        $sponsorHackathon = $entityManager->getRepository(SponsorHackathon::class)->find($id);
        if (!$sponsorHackathon) {
            $this->addFlash('error', 'Assignment not found.');
            return $this->redirectToRoute('app_back_sponsor_hackathon_index');
        }
        $signatureData = $request->request->get('signature');
        if (empty($signatureData) || !preg_match('/^data:image\/png;base64,/', $signatureData)) {
            $this->addFlash('error', 'Please draw your signature first.');
            return $this->redirectToRoute('app_back_sponsor_hackathon_success', ['id' => $id]);
        }

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $dompdf = new Dompdf($pdfOptions);
        $signedAt = new \DateTimeImmutable();
        $html = $this->renderView('backoffice/sponsor_hackathon/pdf_contract.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
            'signature_data' => $signatureData,
            'signed_at' => $signedAt,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        try {
            $emailService->sendContractSignedEmail($sponsorHackathon, $signedAt);
            $this->addFlash('success', 'The signed contract has been sent to ' . $this->getParameter('contract_signed_email'));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'The PDF was generated but the email could not be sent: ' . $e->getMessage());
        }

        // Store signature in session (no DB) so the contract PDF shows it when they open it again
        $request->getSession()->set('contract_signature_' . $id, [
            'signature_data' => $signatureData,
            'signed_at' => $signedAt->format('c'),
        ]);

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="contract_signed_' . $sponsorHackathon->getId() . '.pdf"',
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
        if ($this->isCsrfTokenValid('delete' . $sponsorHackathon->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsorHackathon);
            $entityManager->flush();
            $this->addFlash('success', 'Sponsor removed from hackathon successfully!');
        }

        return $this->redirectToRoute('app_back_sponsor_hackathon_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_back_sponsor_hackathon_pdf', methods: ['GET'])]
    public function downloadPdf(SponsorHackathon $sponsorHackathon, Request $request): Response
    {
        $id = $sponsorHackathon->getId();
        $signatureData = null;
        $signedAt = null;
        $sessionData = $request->getSession()->get('contract_signature_' . $id);
        if ($sessionData && !empty($sessionData['signature_data'])) {
            $signatureData = $sessionData['signature_data'];
            $signedAt = isset($sessionData['signed_at']) ? new \DateTimeImmutable($sessionData['signed_at']) : null;
        }

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('backoffice/sponsor_hackathon/pdf_contract.html.twig', [
            'sponsor_hackathon' => $sponsorHackathon,
            'signature_data' => $signatureData,
            'signed_at' => $signedAt,
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
