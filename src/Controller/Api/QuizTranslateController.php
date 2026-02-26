<?php

namespace App\Controller\Api;

use App\Entity\Quiz;
use App\Service\LibreTranslateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizTranslateController extends AbstractController
{
    #[Route('/api/quizzes/{id}/translate', name: 'api_quiz_translate', methods: ['GET'])]
    public function translate(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        LibreTranslateService $translator
    ): JsonResponse {
        // --- Resolve target language (default: en) ---
        $targetLang = $request->query->get('target_lang', 'en');
        $sourceLang = $request->query->get('source_lang', 'fr');

        // --- Fetch quiz ---
        $quiz = $em->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return $this->json(['error' => 'Quiz not found'], 404);
        }

        // --- Translate quiz title ---
        $translatedTitle = $translator->translate(
            $quiz->getTitle() ?? '',
            $sourceLang,
            $targetLang
        );

        // --- Build translated questions + answers ---
        $translatedQuestions = [];
        foreach ($quiz->getQuestions() as $question) {
            $translatedContent = $translator->translate(
                $question->getContent() ?? '',
                $sourceLang,
                $targetLang
            );

            $translatedAnswers = [];
            foreach ($question->getAnswers() as $answer) {
                $translatedAnswerContent = $translator->translate(
                    $answer->getContent() ?? '',
                    $sourceLang,
                    $targetLang
                );

                $translatedAnswers[] = [
                    'id'         => $answer->getId(),
                    'content'    => $translatedAnswerContent,
                    'is_correct' => $answer->isCorrect(),
                ];
            }

            $translatedQuestions[] = [
                'id'      => $question->getId(),
                'content' => $translatedContent,
                'answers' => $translatedAnswers,
            ];
        }

        // --- Return structured JSON (nothing saved to DB) ---
        return $this->json([
            'original_quiz_id' => $quiz->getId(),
            'translated'       => [
                'title'     => $translatedTitle,
                'questions' => $translatedQuestions,
            ],
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ]);
    }
}
