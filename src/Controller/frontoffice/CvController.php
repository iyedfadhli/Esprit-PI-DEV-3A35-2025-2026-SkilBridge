<?php

namespace App\Controller\frontoffice;

use App\Entity\Cv;
use App\Entity\User;
use App\Entity\Experience;
use App\Entity\Education;
use App\Entity\Skill;
use App\Entity\Certif;
use App\Entity\Langue;
use App\Entity\Offer;
use App\Entity\CvApplication;
use App\Form\CvType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/preview/front')]
class CvController extends AbstractController
{
    // ===================== CV INDEX =====================
    #[Route('/cv', name: 'preview_front_cv_index')]
    public function cvIndex(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }

        $cvs = $em->getRepository(Cv::class)->findBy(['user' => $userId]);

        return $this->render('frontoffice/cv/index.html.twig', [
            'cvs' => $cvs,
        ]);
    }

    // ===================== CV NEW =====================
    #[Route('/cv/new', name: 'preview_front_cv_new', methods: ['GET','POST'])]
    public function cvNew(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);

        $cv = new Cv();
        $cv->setUser($user);
        $cv->setCreationDate(new \DateTime());
        $cv->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cv);
            $em->flush();

            $this->updateCvProgression($cv, $em);

            return $this->redirectToRoute('preview_front_cv_index');
        }

        return $this->render('frontoffice/cv/new.html.twig', [
            'form' => $form->createView(),
            'cv' => $cv,
        ]);
    }

    // ===================== CV EDIT =====================
    #[Route('/cv/{id}/edit', name: 'preview_front_cv_edit', methods: ['GET','POST'])]
    public function cvEdit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $cv = $em->getRepository(Cv::class)->findOneBy(['id' => $id, 'user' => $userId]);
        if (!$cv) throw $this->createNotFoundException("CV not found or access denied");

        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cv->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($cv);
            $em->flush();

            $this->updateCvProgression($cv, $em);

            return $this->redirectToRoute('preview_front_cv_index');
        }

        return $this->render('frontoffice/cv/edit.html.twig', [
            'form' => $form->createView(),
            'cv' => $cv,
        ]);
    }

    // ===================== CV SHOW =====================
    #[Route('/cv/{id}', name: 'preview_front_cv_show')]
    public function cvShow(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $cv = $em->getRepository(Cv::class)->findOneBy(['id' => $id, 'user' => $userId]);
        if (!$cv) throw $this->createNotFoundException("CV not found or access denied");

        return $this->render('frontoffice/cv/show.html.twig', [
            'cv' => $cv,
            'experiences' => $em->getRepository(Experience::class)->findBy(['cv' => $cv]),
            'educations' => $em->getRepository(Education::class)->findBy(['cv' => $cv]),
            'skills' => $em->getRepository(Skill::class)->findBy(['cv' => $cv]),
            'languages' => $em->getRepository(Langue::class)->findBy(['cv' => $cv]),
            'certifs' => $em->getRepository(Certif::class)->findBy(['cv' => $cv]),
        ]);
    }

  #[Route('/cv/{id}/delete', name: 'preview_front_cv_delete', methods: ['POST'])]
public function cvDelete(int $id, Request $request, EntityManagerInterface $em): Response
{
    $userId = $request->getSession()->get('user_id');
    $cv = $em->getRepository(Cv::class)->findOneBy(['id' => $id, 'user' => $userId]);
    if (!$cv) {
        throw $this->createNotFoundException("CV not found or access denied");
    }

    // CSRF check
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete'.$cv->getId(), $token)) {
        $this->addFlash('error', 'Jeton CSRF invalide.');
        return $this->redirectToRoute('preview_front_cv_index');
    }

    $cvId = $cv->getId();

    // Delete related Skills
    $skills = $em->getRepository(Skill::class)->findBy(['cv' => $cvId]);
    foreach ($skills as $skill) {
        $em->remove($skill);
    }

    // Delete related Experiences
    $experiences = $em->getRepository(Experience::class)->findBy(['cv' => $cvId]);
    foreach ($experiences as $exp) {
        $em->remove($exp);
    }

    // Delete related Educations
    $educations = $em->getRepository(Education::class)->findBy(['cv' => $cvId]);
    foreach ($educations as $edu) {
        $em->remove($edu);
    }

    // Delete related Certificates
    $certificates = $em->getRepository(Certif::class)->findBy(['cv' => $cvId]);
    foreach ($certificates as $cert) {
        $em->remove($cert);
    }

    // Delete related Languages
    $languages = $em->getRepository(Langue::class)->findBy(['cv' => $cvId]);
    foreach ($languages as $lang) {
        $em->remove($lang);
    }

    // Delete related Applications
    $applications = $em->getRepository(CvApplication::class)->findBy(['cv' => $cvId]);
    foreach ($applications as $app) {
        $em->remove($app);
    }

    // Finally delete the CV
    $em->remove($cv);
    $em->flush();

    $this->addFlash('success', 'CV supprimé avec succès !');
    return $this->redirectToRoute('preview_front_cv_index');
}





//PDDDDDFFFFFF


#[Route('/cv/{id}/pdf', name: 'user_cv_pdf')]
public function generateCvPdf(int $id, EntityManagerInterface $em): Response
{
    $cv = $em->getRepository(Cv::class)->find($id);
    if (!$cv) {
        throw $this->createNotFoundException("CV not found");
    }

    // Configure Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);

    // Render the Twig template as HTML
    $html = $this->renderView('frontoffice/cv/pdf.html.twig', [
        'cv' => $cv,
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Stream the PDF to the browser
    return new Response($dompdf->stream("CV-{$cv->getnomCv()}.pdf", ["Attachment" => true]), 
        200, 
        ['Content-Type' => 'application/pdf']
    );
}





























    // ===================== OFFERS LIST =====================
    #[Route('/offers', name: 'preview_front_offer_index')]
    public function offerIndex(EntityManagerInterface $em): Response
    {
        $offers = $em->getRepository(Offer::class)->findBy(['status' => 'active']);

        return $this->render('frontoffice/offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    //===================== OFFER SHOW =====================
#[Route('/offers/{id}', name: 'preview_front_offer_show')]
public function offerShow(int $id, Request $request, EntityManagerInterface $em): Response
{
    $offer = $em->getRepository(Offer::class)->find($id);
    if (!$offer) throw $this->createNotFoundException("Offer not found");

    // Get user from session (manual auth used in this project)
    $userId = $request->getSession()->get('user_id');
    $userCvs = [];
    if ($userId) {
        $userCvs = $em->getRepository(Cv::class)->findBy(['user' => $userId]);
    }

    return $this->render('frontoffice/offer/show.html.twig', [
        'offer' => $offer,
        'user_cvs' => $userCvs,
    ]);
}


 // ===================== APPLY TO OFFER =====================
#[Route('/offers/{id}/apply', name: 'preview_front_offer_apply', methods: ['POST'])]
public function applyOffer(int $id, Request $request, EntityManagerInterface $em): Response
{
    // Find the offer
    $offer = $em->getRepository(Offer::class)->find($id);
    if (!$offer) {
        throw $this->createNotFoundException("Offer not found");
    }

    // Get the selected CV id from the form
    $cvId = $request->request->get('cv_id');

    // CSRF check
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('apply' . $offer->getId(), $token)) {
        $this->addFlash('error', 'Jeton CSRF invalide.');
        return $this->redirectToRoute('preview_front_offer_show', ['id' => $id]);
    }

    if (!$cvId) {
        $this->addFlash('error', 'Veuillez sélectionner un CV.');
        return $this->redirectToRoute('preview_front_offer_show', ['id' => $id]);
    }

    // Find the CV
    $cv = $em->getRepository(Cv::class)->find($cvId);
    if (!$cv) {
        throw $this->createNotFoundException("CV not found");
    }

    // Check if the CV has already applied to this offer
    $existingApp = $em->getRepository(CvApplication::class)->findOneBy([
        'cv' => $cv,
        'offer' => $offer,
    ]);

    if ($existingApp) {
        $this->addFlash('warning', 'You have already applied to this offer.');
        return $this->redirectToRoute('preview_front_offer_show', ['id' => $id]);
    }

    // Current logged-in user from session
    $userId = $request->getSession()->get('user_id');
    if (!$userId || $cv->getUser()->getId() !== $userId) {
        $this->addFlash('error', 'Candidature invalide.');
        return $this->redirectToRoute('preview_front_offer_show', ['id' => $id]);
    }

    // Create the application
    $application = new CvApplication();
    $application->setCv($cv);
    $application->setOffer($offer);
  
    $application->setStatus('pending');
    $application->setAppliedAt(new \DateTimeImmutable());

    $em->persist($application);
    $em->flush();

    $this->addFlash('success', 'Application submitted successfully!');
    return $this->redirectToRoute('preview_front_offer_index');
}

    // ===================== HELPER METHOD =====================
    private function updateCvProgression(Cv $cv, EntityManagerInterface $em): void
    {
        $experiencesCount = $em->getRepository(Experience::class)->count(['cv' => $cv]);
        $educationsCount = $em->getRepository(Education::class)->count(['cv' => $cv]);
        $skillsCount = $em->getRepository(Skill::class)->count(['cv' => $cv]);
        $certsCount = $em->getRepository(Certif::class)->count(['cv' => $cv]);
        $languagesCount = $em->getRepository(Langue::class)->count(['cv' => $cv]);

        $progression = 0;
        $progression += $experiencesCount > 0 ? 20 : 0;
        $progression += $educationsCount > 0 ? 20 : 0;
        $progression += $skillsCount > 0 ? 20 : 0;
        $progression += $certsCount > 0 ? 20 : 0;
        $progression += $languagesCount > 0 ? 20 : 0;

        $cv->setProgression($progression);
        $em->flush();
    }
}


