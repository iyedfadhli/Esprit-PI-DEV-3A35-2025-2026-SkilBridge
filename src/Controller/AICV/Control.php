<?php

namespace App\Controller\AICV;

use App\Entity\Cv;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Skill;
use App\Entity\User;
use App\Service\AIgeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Control extends AbstractController
{
    private EntityManagerInterface $em;
    private AIgeneratorService $ai;

    public function __construct(EntityManagerInterface $em, AIgeneratorService $ai)
    {
        $this->em = $em;
        $this->ai = $ai;
    }

    #[Route('/cv/ai/create', name: 'cv_ai_create', methods: ['GET'])]
    public function create(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        return $this->render('frontoffice/cv/ai_create.html.twig');
    }

    #[Route('/cv/ai/generate', name: 'cv_ai_generate', methods: ['POST'])]
    public function generate(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $this->em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cv_ai_generate', $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('cv_ai_create');
        }

        $language = (string)$request->request->get('language', 'French');
        $jobTitle = (string)$request->request->get('job_title', '');
        $description = (string)$request->request->get('description', '');

        try {
            $data = $this->ai->generateCvData($language, $jobTitle, $description);
        } catch (\Throwable $e) {
            $msg = (string)$e->getMessage();
            if (str_contains($msg, 'configuration missing')) {
                $this->addFlash('error', 'Configuration IA absente. Définissez HF_API_TOKEN et HF_MODEL (ou AI_API_URL/AI_API_KEY/AI_MODEL).');
            } elseif (str_contains($msg, 'model is loading')) {
                $this->addFlash('warning', 'Le modèle IA se charge chez Hugging Face. Réessayez dans quelques secondes.');
            } elseif (str_contains($msg, 'API error') || str_contains($msg, 'HTTP request failed')) {
                $this->addFlash('error', 'Erreur de communication avec le fournisseur IA. Vérifiez la clé/modèle et réessayez.');
            } elseif (str_contains($msg, 'invalid JSON') || str_contains($msg, 'Unexpected AI response shape')) {
                $this->addFlash('error', 'La réponse de l’IA n’est pas au bon format. Réessayez ou changez de modèle.');
            } else {
                $this->addFlash('error', 'La génération IA a échoué. Réessayez plus tard.');
            }
            return $this->redirectToRoute('cv_ai_create');
        }

        $cvData = $data['cv'];
        $cv = new Cv();
        $cv->setNomCv($cvData['nomCv'] ?: ('CV - ' . ($jobTitle ?: 'Profil')));
        $cv->setSummary($cvData['summary'] ?: '');
        $cv->setLangue($cvData['langue'] ?: 'Francais');
        $cv->setCreationDate(new \DateTime());
        $cv->setUpdatedAt(new \DateTimeImmutable());
        $cv->setUser($user);

        $this->em->persist($cv);
        $this->em->flush();

        foreach ($data['experiences'] as $e) {
            $exp = new Experience();
            $exp->setCv($cv);
            $exp->setJobTitle($e['job_title'] ?: 'Experience');
            $exp->setCompany($e['company'] ?: 'Company');
            $exp->setLocation($e['location'] ?: null);
            $sd = $this->toDateOrNull($e['start_date'] ?? null);
            $ed = $this->toDateOrNull($e['end_date'] ?? null);
            $exp->setStartDate($sd);
            $exp->setEndDate($ed);
            $exp->setCurrentlyWorking((bool)($e['currently_working'] ?? false));
            $exp->setDescription($e['description'] ?: '');
            $this->em->persist($exp);
        }

        foreach ($data['educations'] as $ed) {
            $edu = new Education();
            $edu->setCv($cv);
            $edu->setDegree($ed['degree'] ?: 'Diplome');
            $edu->setFieldOfStudy($ed['field_of_study'] ?: null);
            $edu->setSchool($ed['school'] ?: 'Ecole');
            $edu->setCity($ed['city'] ?: null);
            $sd = $this->toDateOrDefault($ed['start_date'] ?? null);
            $edate = $this->toDateOrDefault($ed['end_date'] ?? null);
            $edu->setStartDate($sd);
            $edu->setEndDate($edate);
            $edu->setDescription($ed['description'] ?: null);
            $this->em->persist($edu);
        }

        foreach ($data['skills'] as $sk) {
            $skill = new Skill();
            $skill->setCv($cv);
            $skill->setNom($sk['nom'] ?: 'Skill');
            $skill->setType($sk['type'] ?: 'tech');
            $skill->setLevel($sk['level'] ?: 'Intermediaire');
            $this->em->persist($skill);
        }

        $this->em->flush();

        return $this->redirectToRoute('preview_front_cv_show', ['id' => $cv->getId()]);
    }

    private function toDateOrNull(?string $s): ?\DateTime
    {
        if (!$s) {
            return null;
        }
        try {
            return new \DateTime($s);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toDateOrDefault(?string $s): \DateTime
    {
        try {
            return new \DateTime($s ?: date('Y-m-01'));
        } catch (\Throwable $e) {
            return new \DateTime(date('Y-m-01'));
        }
    }
}
