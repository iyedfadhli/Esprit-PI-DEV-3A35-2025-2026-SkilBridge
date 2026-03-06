<?php

namespace App\Controller\Api;

use App\Entity\QuizAttempts;
use App\Service\AiFeedbackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur API pour le feedback pédagogique IA.
 *
 * Endpoint : GET /api/quiz/attempts/{attemptId}/ai-feedback
 * Génère un feedback ultra-personnalisé via Google Gemini 1.5 Flash
 * en analysant les réponses de l'étudiant et les sections_to_review du cours.
 */
class AiFeedbackController extends AbstractController
{
    public function __construct(
        private readonly AiFeedbackService $feedbackService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Génère un feedback IA personnalisé pour une tentative de quiz.
     *
     * Analyse :
     * - Les réponses de l'étudiant (correctes + incorrectes)
     * - Les sections_to_review du cours
     * - Le niveau de difficulté et le contenu du cours
     *
     * Retourne un JSON avec feedback, points forts, lacunes, plan d'action, etc.
     */
    #[Route('/api/quiz/attempts/{attemptId}/ai-feedback', name: 'api_quiz_ai_feedback', methods: ['GET'])]
    public function getAiFeedback(int $attemptId): JsonResponse
    {
        // TODO: Remettre #[IsGranted('ROLE_USER')] quand l'auth sera activée
        $attempt = $this->em->getRepository(QuizAttempts::class)->find($attemptId);

        if (!$attempt) {
            return new JsonResponse(
                ['error' => 'Tentative de quiz introuvable.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // Vérifier que la tentative est bien soumise
        if ($attempt->getStatus() !== 'SUBMITTED' && $attempt->getStatus() !== 'EXPIRED') {
            return new JsonResponse(
                ['error' => 'Le feedback n\'est disponible que pour les tentatives soumises.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $feedback = $this->feedbackService->generateFeedback($attempt);

            return new JsonResponse($feedback, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Erreur lors de la génération du feedback IA.', 'details' => $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
