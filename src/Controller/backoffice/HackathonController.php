<?php

namespace App\Controller\backoffice;

use App\Entity\Hackathon;
use App\Form\HackathonType;
use App\Repository\HackathonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/backoffice/hackathon')]
class HackathonController extends AbstractController
{
    #[Route('/', name: 'app_back_hackathon_index', methods: ['GET'])]
    public function index(Request $request, HackathonRepository $hackathonRepository): Response
    {
        $query = $request->query->get('q');
        $status = $request->query->get('status');
        
        $hackathons = $hackathonRepository->searchHackathons($query, $status);

        if ($request->query->get('ajax')) {
            return $this->render('backoffice/hackathon/_table_body.html.twig', [
                'hackathons' => $hackathons,
            ]);
        }

        return $this->render('backoffice/hackathon/index.html.twig', [
            'hackathons' => $hackathons,
        ]);
    }

    #[Route('/new', name: 'app_back_hackathon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $hackathon = new Hackathon();
        $form = $this->createForm(HackathonType::class, $hackathon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover_url')->getData();

            if ($coverFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/hackathons',
                        $newFilename
                    );
                    $hackathon->setCoverUrl('/uploads/hackathons/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }
            }

            $users = $userRepository->findAll();
            if (!empty($users)) {
                $hackathon->setCreatorId($users[0]);
            }

            $entityManager->persist($hackathon);
            $entityManager->flush();

            $this->addFlash('success', 'Hackathon created successfully!');
            return $this->redirectToRoute('app_back_hackathon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/hackathon/new.html.twig', [
            'hackathon' => $hackathon,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_back_hackathon_show', methods: ['GET'])]
    public function show(Hackathon $hackathon): Response
    {
        return $this->render('backoffice/hackathon/show.html.twig', [
            'hackathon' => $hackathon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_hackathon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hackathon $hackathon, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HackathonType::class, $hackathon);
        $form->remove('cover_url');
        $form->add('cover_url', FileType::class, [
            'label' => 'Cover Image (Leave empty to keep current)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new Assert\File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, WEBP)',
                ])
            ],
            'attr' => ['class' => 'form-control'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover_url')->getData();

            if ($coverFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/hackathons',
                        $newFilename
                    );
                    $hackathon->setCoverUrl('/uploads/hackathons/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Hackathon updated successfully!');
            return $this->redirectToRoute('app_back_hackathon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/hackathon/edit.html.twig', [
            'hackathon' => $hackathon,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_back_hackathon_delete', methods: ['POST'])]
    public function delete(Request $request, Hackathon $hackathon, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hackathon->getId(), $request->request->get('_token'))) {
            $entityManager->remove($hackathon);
            $entityManager->flush();
            $this->addFlash('success', 'Hackathon deleted successfully!');
        }

        return $this->redirectToRoute('app_back_hackathon_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_back_hackathon_pdf', methods: ['GET'])]
    public function downloadPdf(Hackathon $hackathon): Response
    {
        // 1. Configure DomPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($pdfOptions);

        // 2. Render HTML
        $html = $this->renderView('backoffice/hackathon/pdf_details.html.twig', [
            'hackathon' => $hackathon,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 3. Return PDF Response
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="hackathon_' . $hackathon->getId() . '_' . str_replace(' ', '_', $hackathon->getTitle()) . '.pdf"',
        ]);
    }
}
