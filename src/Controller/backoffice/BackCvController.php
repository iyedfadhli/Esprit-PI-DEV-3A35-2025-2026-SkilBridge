<?php

namespace App\Controller\backoffice;

use App\Entity\Cv;
use App\Entity\Experience;
use App\Entity\Education;
use App\Entity\Skill;
use App\Entity\Certif;
use App\Entity\Langue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/preview/back')]
class BackCvController extends AbstractController
{
    // ===================== CV INDEX =====================
    #[Route('/cv', name: 'preview_back_cv_index')]
    public function cvIndex(EntityManagerInterface $em): Response
    {
        $cvs = $em->getRepository(Cv::class)->findBy([], [], 99);

        return $this->render('backoffice/cv/index.html.twig', [
            'cvs' => $cvs,
        ]);
    }

    // ===================== CV SHOW =====================
    #[Route('/cv/{id}', name: 'preview_back_cv_show')]
    public function cvShow(int $id, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($id);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        // Fetch related entities using repository
        $experiences = $em->getRepository(Experience::class)->findBy(['cv' => $cv]);
        $educations = $em->getRepository(Education::class)->findBy(['cv' => $cv]);
        $skills = $em->getRepository(Skill::class)->findBy(['cv' => $cv]);
        $certifs = $em->getRepository(Certif::class)->findBy(['cv' => $cv]);
        $languages = $em->getRepository(Langue::class)->findBy(['cv' => $cv]);

        return $this->render('backoffice/cv/show.html.twig', [
            'cv' => $cv,
            'experiences' => $experiences,
            'educations' => $educations,
            'skills' => $skills,
            'certifs' => $certifs,
            'languages' => $languages,
        ]);
    }
// ===================== CV DELETE =====================
#[Route('/cv/{id}/delete', name: 'preview_back_cv_delete', methods: ['POST'])]
public function cvDelete(
    int $id,
    Request $request,
    EntityManagerInterface $em
): Response {
    $cv = $em->getRepository(Cv::class)->find($id);

    if (!$cv) {
        throw $this->createNotFoundException('CV not found');
    }

    // ✅ CSRF protection
    if (!$this->isCsrfTokenValid('delete_cv_' . $cv->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('preview_back_cv_index');
    }

    // ===================== DELETE CHILD ENTITIES =====================

    foreach ($em->getRepository(Experience::class)->findBy(['cv' => $cv]) as $exp) {
        $em->remove($exp);
    }

    foreach ($em->getRepository(Education::class)->findBy(['cv' => $cv]) as $edu) {
        $em->remove($edu);
    }

    foreach ($em->getRepository(Skill::class)->findBy(['cv' => $cv]) as $skill) {
        $em->remove($skill);
    }

    foreach ($em->getRepository(Certif::class)->findBy(['cv' => $cv]) as $certif) {
        $em->remove($certif);
    }

    foreach ($em->getRepository(Langue::class)->findBy(['cv' => $cv]) as $lang) {
        $em->remove($lang);
    }

    // ===================== DELETE CV =====================
    $em->remove($cv);
    $em->flush();

    $this->addFlash('success', 'CV deleted successfully.');

    return $this->redirectToRoute('preview_back_cv_index');
}


    // ===================== SKILL CRUD =====================
    #[Route('/cv/{cvId}/skill/{id}/edit', name: 'preview_back_skill_edit', methods: ['GET', 'POST'])]
    public function editSkill(int $cvId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $skill = $em->getRepository(Skill::class)->find($id);
        if (!$skill || $skill->getCv() !== $cv) {
            throw $this->createNotFoundException("Skill not found");
        }

        if ($request->isMethod('POST')) {
            if ($request->request->get('nom')) $skill->setNom($request->request->get('nom'));
            if ($request->request->get('type')) $skill->setType($request->request->get('type'));
            if ($request->request->get('level')) $skill->setLevel($request->request->get('level'));

            $em->flush();
            $this->addFlash('success', 'Skill updated!');
            return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
        }

        return $this->render('backoffice/cv/skill_edit.html.twig', [
            'cv' => $cv,
            'skill' => $skill,
        ]);
    }

    #[Route('/cv/{cvId}/skill/{id}/delete', name: 'preview_back_skill_delete', methods: ['POST'])]
    public function deleteSkill(int $cvId, int $id, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $skill = $em->getRepository(Skill::class)->find($id);
        if (!$skill || $skill->getCv() !== $cv) {
            throw $this->createNotFoundException("Skill not found");
        }

        $em->remove($skill);
        $em->flush();

        $this->addFlash('success', 'Skill deleted!');
        return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
    }

    // ===================== CERTIFICATION CRUD =====================
    #[Route('/cv/{cvId}/certif/{id}/edit', name: 'preview_back_certif_edit', methods: ['GET', 'POST'])]
    public function editCertif(int $cvId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $certif = $em->getRepository(Certif::class)->find($id);
        if (!$certif || $certif->getCv() !== $cv) {
            throw $this->createNotFoundException("Certification not found");
        }

        if ($request->isMethod('POST')) {
            if ($request->request->get('name')) $certif->setName($request->request->get('name'));
            if ($request->request->get('issued_by')) $certif->setIssuedBy($request->request->get('issued_by'));
            if ($request->request->get('issue_date')) $certif->setIssueDate(new \DateTime($request->request->get('issue_date')));
            if ($request->request->get('exp_date')) $certif->setExpDate(new \DateTime($request->request->get('exp_date')));

            $em->flush();
            $this->addFlash('success', 'Certification updated!');
            return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
        }

        return $this->render('backoffice/cv/certif_edit.html.twig', [
            'cv' => $cv,
            'certif' => $certif,
        ]);
    }

    #[Route('/cv/{cvId}/certif/{id}/delete', name: 'preview_back_certif_delete', methods: ['POST'])]
    public function deleteCertif(int $cvId, int $id, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $certif = $em->getRepository(Certif::class)->find($id);
        if (!$certif || $certif->getCv() !== $cv) {
            throw $this->createNotFoundException("Certification not found");
        }

        $em->remove($certif);
        $em->flush();

        $this->addFlash('success', 'Certification deleted!');
        return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
    }

    // ===================== LANGUAGE CRUD =====================
    #[Route('/cv/{cvId}/langue/{id}/edit', name: 'preview_back_langue_edit', methods: ['GET', 'POST'])]
    public function editLangue(int $cvId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $langue = $em->getRepository(Langue::class)->find($id);
        if (!$langue || $langue->getCv() !== $cv) {
            throw $this->createNotFoundException("Language not found");
        }

        if ($request->isMethod('POST')) {
            if ($request->request->get('nom')) $langue->setNom($request->request->get('nom'));
            if ($request->request->get('niveau')) $langue->setNiveau($request->request->get('niveau'));

            $em->flush();
            $this->addFlash('success', 'Language updated!');
            return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
        }

        return $this->render('backoffice/cv/langue_edit.html.twig', [
            'cv' => $cv,
            'langue' => $langue,
        ]);
    }

    #[Route('/cv/{cvId}/langue/{id}/delete', name: 'preview_back_langue_delete', methods: ['POST'])]
    public function deleteLangue(int $cvId, int $id, EntityManagerInterface $em): Response
    {
        $cv = $em->getRepository(Cv::class)->find($cvId);
        if (!$cv) {
            throw $this->createNotFoundException("CV not found");
        }

        $langue = $em->getRepository(Langue::class)->find($id);
        if (!$langue || $langue->getCv() !== $cv) {
            throw $this->createNotFoundException("Language not found");
        }

        $em->remove($langue);
        $em->flush();

        $this->addFlash('success', 'Language deleted!');
        return $this->redirectToRoute('preview_back_cv_show', ['id' => $cvId]);
    }
}
