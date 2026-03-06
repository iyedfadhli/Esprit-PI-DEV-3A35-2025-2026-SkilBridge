<?php

namespace App\Controller\frontoffice;

use App\Entity\Cv;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Skill;
use App\Service\TranslatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CvTranslationController extends AbstractController
{
    private EntityManagerInterface $em;
    private TranslatorService $translator;

    public function __construct(EntityManagerInterface $em, TranslatorService $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    #[Route('/cv/{id}/translate', name: 'cv_translate_select', methods: ['GET'])]
    public function select(int $id): Response
    {
        $cv = $this->em->getRepository(Cv::class)->find($id);
        if (!$cv) {
            throw $this->createNotFoundException('CV introuvable');
        }

        $experiences = $this->em->getRepository(Experience::class)->findBy(['cv' => $cv]);
        $educations = $this->em->getRepository(Education::class)->findBy(['cv' => $cv]);
        $skills = $this->em->getRepository(Skill::class)->findBy(['cv' => $cv]);

        return $this->render('frontoffice/cv/translate.html.twig', [
            'cv' => $cv,
            'experiences' => $experiences,
            'educations' => $educations,
            'skills' => $skills,
            'lang' => null,
            'translations' => null,
        ]);
    }

    #[Route('/cv/{id}/translate/{lang}', name: 'cv_translate', methods: ['GET'], requirements: ['lang' => 'fr|ang|de'])]
    public function translate(int $id, string $lang): Response
    {
        $cv = $this->em->getRepository(Cv::class)->find($id);
        if (!$cv) {
            throw $this->createNotFoundException('CV introuvable');
        }

        $experiences = $this->em->getRepository(Experience::class)->findBy(['cv' => $cv]);
        $educations = $this->em->getRepository(Education::class)->findBy(['cv' => $cv]);
        $skills = $this->em->getRepository(Skill::class)->findBy(['cv' => $cv]);

        $source = $this->mapSourceLang((string) $cv->getLangue());
        $target = $this->mapTargetLang($lang);

        $translations = [
            'cv' => [],
            'experiences' => [],
            'educations' => [],
            'skills' => [],
        ];

        $translations['cv'] = $this->translator->translateFields([
            'nomCv' => (string) $cv->getNomCv(),
            'summary' => (string) ($cv->getSummary() ?? ''),
        ], $target, $source);

        foreach ($experiences as $idx => $exp) {
            $t = $this->translator->translateFields([
                'job_title' => (string) $exp->getJobTitle(),
                'company' => (string) $exp->getCompany(),
                'location' => (string) ($exp->getLocation() ?? ''),
                'description' => (string) $exp->getDescription(),
            ], $target, $source);
            $translations['experiences'][$idx] = $t;
        }

        foreach ($educations as $idx => $edu) {
            $t = $this->translator->translateFields([
                'degree' => (string) $edu->getDegree(),
                'field_of_study' => (string) ($edu->getFieldOfStudy() ?? ''),
                'school' => (string) $edu->getSchool(),
                'city' => (string) ($edu->getCity() ?? ''),
                'description' => (string) ($edu->getDescription() ?? ''),
            ], $target, $source);
            $translations['educations'][$idx] = $t;
        }

        foreach ($skills as $idx => $sk) {
            $t = $this->translator->translateFields([
                'nom' => (string) $sk->getNom(),
                'type' => (string) $sk->getType(),
                'level' => (string) $sk->getLevel(),
            ], $target, $source);
            $translations['skills'][$idx] = $t;
        }

        return $this->render('frontoffice/cv/translate.html.twig', [
            'cv' => $cv,
            'experiences' => $experiences,
            'educations' => $educations,
            'skills' => $skills,
            'lang' => $lang,
            'translations' => $translations,
        ]);
    }

    #[Route('/cv/{id}/translate/{lang}/save', name: 'cv_translate_save', methods: ['POST'], requirements: ['lang' => 'fr|ang|de'])]
    public function save(int $id, string $lang, Request $request): Response
    {
        $cv = $this->em->getRepository(Cv::class)->find($id);
        if (!$cv) {
            throw $this->createNotFoundException('CV introuvable');
        }
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('translate'.$cv->getId(), is_string($token) ? $token : null)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('cv_translate', ['id' => $id, 'lang' => $lang]);
        }

        $cvData = $request->request->all('cv');
        if (isset($cvData['nomCv'])) {
            $cv->setNomCv($cvData['nomCv'] ?: $cv->getNomCv());
        }
        if (array_key_exists('summary', $cvData)) {
            $cv->setSummary($cvData['summary']);
        }
        // Mettre à jour la langue seulement pour fr/ang (conformément à l'assert Choice)
        if ($lang === 'fr') {
            $cv->setLangue('Francais');
        } elseif ($lang === 'ang') {
            $cv->setLangue('Anglais');
        }

        $exps = $request->request->all('experiences');
        foreach ($exps as $expId => $data) {
            $exp = $this->em->getRepository(Experience::class)->find($expId);
            if ($exp && $exp->getCv() && $exp->getCv()->getId() === $cv->getId()) {
                if (isset($data['jobTitle'])) $exp->setJobTitle($data['jobTitle']);
                if (isset($data['company'])) $exp->setCompany($data['company']);
                if (array_key_exists('location', $data)) $exp->setLocation($data['location']);
                if (array_key_exists('description', $data)) $exp->setDescription($data['description']);
            }
        }

        $edus = $request->request->all('educations');
        foreach ($edus as $eduId => $data) {
            $edu = $this->em->getRepository(Education::class)->find($eduId);
            if ($edu && $edu->getCv() && $edu->getCv()->getId() === $cv->getId()) {
                if (isset($data['degree'])) $edu->setDegree($data['degree']);
                if (isset($data['fieldOfStudy'])) $edu->setFieldOfStudy($data['fieldOfStudy']);
                if (isset($data['school'])) $edu->setSchool($data['school']);
                if (array_key_exists('city', $data)) $edu->setCity($data['city']);
                if (array_key_exists('description', $data)) $edu->setDescription($data['description']);
            }
        }

        $skills = $request->request->all('skills');
        foreach ($skills as $skillId => $data) {
            $sk = $this->em->getRepository(Skill::class)->find($skillId);
            if ($sk && $sk->getCv() && $sk->getCv()->getId() === $cv->getId()) {
                if (isset($data['nom'])) $sk->setNom($data['nom']);
                if (array_key_exists('type', $data)) $sk->setType($data['type']);
                if (array_key_exists('level', $data)) $sk->setLevel($data['level']);
            }
        }

        $this->em->flush();

        $this->addFlash('success', 'Traduction enregistrée avec succès.');
        return $this->redirectToRoute('preview_front_cv_show', ['id' => $cv->getId()]);
    }

    private function mapTargetLang(string $lang): string
    {
        $l = strtolower($lang);
        return match ($l) {
            'ang' => 'en',
            'fr' => 'fr',
            'de' => 'de',
            default => $l,
        };
    }

    private function mapSourceLang(string $langue): string
    {
        $l = strtolower($langue);
        return match ($l) {
            'francais', 'français' => 'fr',
            'anglais' => 'en',
            'arabe' => 'ar',
            default => 'auto',
        };
    }
}
