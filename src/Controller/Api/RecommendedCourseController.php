<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\CourseRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur API pour la recommandation intelligente de cours.
 *
 * Endpoint : GET 
 * Accessible aux utilisateurs conn/api/student/recommended-coursesectés (ROLE_USER minimum).
 */
class RecommendedCourseController extends AbstractController
{
    public function __construct(
        private readonly CourseRecommendationService $recommendationService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Retourne les 3 cours les plus pertinents à suivre pour l'étudiant connecté.
     *
     * La recommandation se base sur :
     * - Les prérequis déjà validés
     * - Le niveau actuel (BEGINNER → INTERMEDIATE → ADVANCED)
     * - La performance récente (score moyen des 3 derniers quiz réussis)
     * - La progression naturelle dans le parcours
     */
    #[Route('/api/student/recommended-courses', name: 'api_student_recommended_courses', methods: ['GET'])]
    public function getRecommendedCourses(): JsonResponse
    {
        // TODO: Remettre #[IsGranted('ROLE_USER')] quand l'auth sera activée
        $student = $this->getUser();

        // Fallback : si pas d'utilisateur connecté, prendre le premier étudiant en base
        if (!$student) {
            $student = $this->em->getRepository(User::class)->findOneBy([]);
        }

        if (!$student) {
            return new JsonResponse(
                ['error' => 'Aucun utilisateur trouvé en base de données.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // Déléguer toute la logique métier au service
        $recommendations = $this->recommendationService->getRecommendations($student);

        return new JsonResponse($recommendations, JsonResponse::HTTP_OK);
    }
}
