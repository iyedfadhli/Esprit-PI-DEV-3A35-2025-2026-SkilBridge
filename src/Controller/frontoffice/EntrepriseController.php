<?php

namespace App\Controller\frontoffice;

use App\Entity\Offer;
use App\Entity\Entreprise;
use App\Entity\User;
use App\Entity\Cv;
use App\Entity\CvApplication;
use App\Entity\Experience;
use App\Entity\Education;
use App\Entity\Skill;
use App\Entity\Certif;
use App\Entity\Langue;
use App\Form\OfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/entreprise')]
class EntrepriseController extends AbstractController
{
    // ===================== MES OFFRES =====================
    #[Route('/offers', name: 'entreprise_offer_index')]
    public function myOffers(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) return $this->redirectToRoute('sign');

        $offers = $em->getRepository(Offer::class)->findBy([
            'entreprise' => $userId
        ]);

        return $this->render('frontoffice/entreprise/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    // ===================== NEW OFFER =====================
    #[Route('/offers/new', name: 'entreprise_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $request->getSession()->get('user_id');
            if (!$userId) return $this->redirectToRoute('sign');
            $entreprise = $em->getRepository(User::class)->find($userId);
            
            $offer->setEntreprise($entreprise);
            $offer->setCreatedAt(new \DateTimeImmutable());

            if (!$offer->getStatus()) {
                $offer->setStatus('active');
            }

            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 'Offer created successfully!');
            return $this->redirectToRoute('entreprise_offer_index');
        }

        return $this->render('frontoffice/entreprise/new_offer.html.twig', [
            'form' => $form->createView(),
            'offer' => $offer,
        ]);
    }

    // ===================== EDIT OFFER =====================
    #[Route('/offers/{id}/edit', name: 'entreprise_offer_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $offer = $em->getRepository(Offer::class)->findOneBy(['id' => $id, 'entreprise' => $userId]);
        if (!$offer) {
            throw $this->createNotFoundException("Offer not found or access denied");
        }

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offer updated successfully!');
            return $this->redirectToRoute('entreprise_offer_index');
        }

        return $this->render('frontoffice/entreprise/edit_offer.html.twig', [
            'form' => $form->createView(),
            'offer' => $offer,
        ]);
    }

    // ===================== DELETE OFFER =====================
    #[Route('/offers/{id}/delete', name: 'entreprise_offer_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $offer = $em->getRepository(Offer::class)->findOneBy(['id' => $id, 'entreprise' => $userId]);
        if (!$offer) {
            throw $this->createNotFoundException("Offer not found or access denied");
        }

        if ($this->isCsrfTokenValid('delete' . $offer->getId(), $request->request->get('_token'))) {
            $applications = $em->getRepository(CvApplication::class)->findBy(['offer' => $offer]);
            foreach ($applications as $application) {
                $em->remove($application);
            }
            
            $em->remove($offer);
            $em->flush();
            $this->addFlash('success', 'Offer deleted successfully!');
        }

        return $this->redirectToRoute('entreprise_offer_index');
    }

    // ===================== VIEW APPLICATIONS =====================
    #[Route('/offers/{id}/applications', name: 'entreprise_offer_applications')]
    public function showApplications(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $offer = $em->getRepository(Offer::class)->findOneBy(['id' => $id, 'entreprise' => $userId]);
        if (!$offer) {
            throw $this->createNotFoundException("Offer not found or access denied");
        }

        $applications = $em->getRepository(CvApplication::class)->findBy(['offer' => $offer]);

        return $this->render('frontoffice/entreprise/applications.html.twig', [
            'offer' => $offer,
            'applications' => $applications,
        ]);
    }

    // ===================== REVIEW CV =====================
    #[Route('/application/{id}/review', name: 'entreprise_application_review')]
    public function reviewCv(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $application = $em->getRepository(CvApplication::class)->find($id);
        
        if (!$application || $application->getOffer()->getEntreprise()->getId() !== $userId) {
            throw $this->createNotFoundException("Application not found or access denied");
        }

        $cv = $application->getCv();

        return $this->render('frontoffice/entreprise/review_cv.html.twig', [
            'application' => $application,
            'cv' => $cv,
            'offer' => $application->getOffer(),
            'experiences' => $em->getRepository(Experience::class)->findBy(['cv' => $cv]),
            'educations' => $em->getRepository(Education::class)->findBy(['cv' => $cv]),
            'skills' => $em->getRepository(Skill::class)->findBy(['cv' => $cv]),
            'languages' => $em->getRepository(Langue::class)->findBy(['cv' => $cv]),
            'certifs' => $em->getRepository(Certif::class)->findBy(['cv' => $cv]),
        ]);
    }

    // ===================== UPDATE STATUS =====================
    #[Route('/application/{id}/status', name: 'entreprise_application_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $application = $em->getRepository(CvApplication::class)->find($id);
        if (!$application) {
            throw $this->createNotFoundException("Application not found");
        }

        $status = $request->request->get('status');
        if (in_array($status, ['accepted', 'rejected', 'pending'])) {
            $application->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Statut mis � jour avec succ�s !');
        }

        return $this->redirectToRoute('entreprise_offer_applications', ['id' => $application->getOffer()->getId()]);
    }
}
