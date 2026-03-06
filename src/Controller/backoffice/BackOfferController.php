<?php

namespace App\Controller\backoffice;

use App\Entity\Offer;
use App\Entity\Entreprise;
use App\Entity\CvApplication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/preview/back')]
class BackOfferController extends AbstractController
{
    // ===================== OFFER INDEX =====================
    #[Route('/offers', name: 'preview_back_offer_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $offers = $em->getRepository(Offer::class)->findBy([], [], 99);

        return $this->render('backoffice/offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    // ===================== OFFER SHOW =====================
    #[Route('/offers/{id}', name: 'preview_back_offer_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offer::class)->find($id);
        if (!$offer) {
            throw $this->createNotFoundException("Offer not found");
        }

        $applications = $em->getRepository(CvApplication::class)->findBy(['offer' => $offer]);

        return $this->render('backoffice/offer/show.html.twig', [
            'offer' => $offer,
            'applications' => $applications,
        ]);
    }

    // ===================== OFFER NEW =====================
    #[Route('/offers/new', name: 'preview_back_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offer = new Offer();

        if ($request->isMethod('POST')) {
            // Enterprise
            $enterpriseId = $request->request->get('enterprise_id');
            $enterprise = $enterpriseId ? $em->getRepository(Entreprise::class)->find($enterpriseId) : null;
            if ($enterpriseId && !$enterprise) {
                throw $this->createNotFoundException("Enterprise not found");
            }

            // Set fields safely
            $offer->setTitle($request->request->get('title', ''));
            $offer->setOfferType($request->request->get('offerType', '')); // camelCase
            $offer->setField($request->request->get('field', ''));
            $offer->setRequiredLevel($request->request->get('requiredLevel', ''));
            $offer->setLocation($request->request->get('location', ''));
            $offer->setContractType($request->request->get('contractType', ''));

            // Duration (int)
            $duration = $request->request->get('duration');
            $offer->setDuration($duration !== null && $duration !== '' ? (int)$duration : null);

            // Salary (float)
            $salary = $request->request->get('salaryRange');
            $offer->setSalaryRange($salary !== null && $salary !== '' ? (float)$salary : null);

            $offer->setDescription($request->request->get('description', ''));
            $offer->setRequiredSkills($request->request->get('requiredSkills', ''));
            $offer->setStatus($request->request->get('status', 'active'));
            $offer->setEntreprise($enterprise);
            $offer->setCreatedAt(new \DateTimeImmutable());

            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 'Offer created successfully!');
            return $this->redirectToRoute('preview_back_offer_index');
        }

        $enterprises = $em->getRepository(Entreprise::class)->findBy([], [], 99);

        return $this->render('backoffice/offer/new.html.twig', [
            'enterprises' => $enterprises,
            'offer' => $offer,
        ]);
    }

    // ===================== OFFER EDIT =====================
    #[Route('/offers/{id}/edit', name: 'preview_back_offer_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offer::class)->find($id);
        if (!$offer) {
            throw $this->createNotFoundException("Offer not found");
        }

        if ($request->isMethod('POST')) {
            // Enterprise
            $enterpriseId = $request->request->get('enterprise_id');
            $enterprise = $enterpriseId ? $em->getRepository(Entreprise::class)->find($enterpriseId) : null;
            if ($enterpriseId && !$enterprise) {
                throw $this->createNotFoundException("Enterprise not found");
            }

            // Update fields safely
            $title = $request->request->get('title');
            if ($title) $offer->setTitle($title);

            $offerType = $request->request->get('offerType');
            if ($offerType) $offer->setOfferType($offerType);

            $field = $request->request->get('field');
            if ($field) $offer->setField($field);

            $requiredLevel = $request->request->get('requiredLevel');
            if ($requiredLevel) $offer->setRequiredLevel($requiredLevel);

            $location = $request->request->get('location');
            if ($location) $offer->setLocation($location);

            $contractType = $request->request->get('contractType');
            if ($contractType) $offer->setContractType($contractType);

            // Duration
            $duration = $request->request->get('duration');
            $offer->setDuration($duration !== null && $duration !== '' ? (int)$duration : null);

            // Salary
            $salary = $request->request->get('salaryRange');
            $offer->setSalaryRange($salary !== null && $salary !== '' ? (float)$salary : null);

            $description = $request->request->get('description');
            if ($description) $offer->setDescription($description);

            $requiredSkills = $request->request->get('requiredSkills');
            if ($requiredSkills) $offer->setRequiredSkills($requiredSkills);

            $status = $request->request->get('status');
            if ($status) $offer->setStatus($status);

            $offer->setEntreprise($enterprise);

            $em->flush();

            $this->addFlash('success', 'Offer updated successfully!');
            return $this->redirectToRoute('preview_back_offer_show', ['id' => $id]);
        }

        $enterprises = $em->getRepository(Entreprise::class)->findBy([], [], 99);

        return $this->render('backoffice/offer/edit.html.twig', [
            'offer' => $offer,
            'enterprises' => $enterprises,
        ]);
    }

// ===================== OFFER DELETE =====================
#[Route('/offers/{id}/delete', name: 'preview_back_offer_delete', methods: ['POST'])]
public function delete(int $id, Request $request, EntityManagerInterface $em): Response
{
    $offer = $em->getRepository(Offer::class)->find($id);

    if (!$offer) {
        throw $this->createNotFoundException('Offer not found');
    }

    // Remove related applications first (FK constraint safety)
    $applications = $em->getRepository(CvApplication::class)
        ->findBy(['offer' => $offer]);

    foreach ($applications as $application) {
        $em->remove($application);
    }

    // Remove offer
    $em->remove($offer);
    $em->flush();

    $this->addFlash('success', 'Offer deleted successfully!');

    return $this->redirectToRoute('preview_back_offer_index');
}

     // ===================== LIST APPLICATIONS =====================
    #[Route('/applications', name: 'preview_back_application_index')]
    public function applicationIndex(EntityManagerInterface $em): Response
    {
        $applications = $em->getRepository(CvApplication::class)->findBy([], [], 99);

        return $this->render('backoffice/application/index.html.twig', [
            'applications' => $applications,
        ]);
    }

    // ===================== SHOW SINGLE APPLICATION =====================
    #[Route('/applications/{id}', name: 'preview_back_application_show')]
    public function applicationShow(int $id, EntityManagerInterface $em): Response
    {
        $application = $em->getRepository(CvApplication::class)->find($id);

        if (!$application) {
            throw $this->createNotFoundException("Application not found");
        }

        return $this->render('backoffice/application/show.html.twig', [
            'application' => $application,
        ]);
    }

    // ===================== UPDATE APPLICATION STATUS =====================
    #[Route('/applications/{id}/status', name: 'preview_back_application_update_status', methods: ['POST'])]
    public function updateApplicationStatus(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $application = $em->getRepository(CvApplication::class)->find($id);
        if (!$application) {
            throw $this->createNotFoundException("Application not found");
        }

        $status = $request->request->get('status');
        if (in_array($status, ['pending', 'accepted', 'rejected', 'interview'])) {
            $application->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Application status updated!');
        }

        return $this->redirectToRoute('preview_back_application_show', ['id' => $id]);
    }

    // ===================== DELETE APPLICATION =====================
    #[Route('/applications/{id}/delete', name: 'preview_back_application_delete', methods: ['POST'])]
    public function deleteApplication(int $id, EntityManagerInterface $em): Response
    {
        $application = $em->getRepository(CvApplication::class)->find($id);
        if (!$application) {
            throw $this->createNotFoundException("Application not found");
        }

        $em->remove($application);
        $em->flush();

        $this->addFlash('success', 'Application deleted successfully!');
        return $this->redirectToRoute('preview_back_application_index');
    }
}
