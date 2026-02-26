<?php

namespace App\Service;

use App\Entity\QuizAttempts;
use App\Entity\StudentResponse;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de feedback pédagogique personnalisé par IA.
 *
 * Analyse les réponses d'un étudiant à un quiz + les sections_to_review du cours
 * et génère un feedback ultra-personnalisé via Google Gemini 1.5 Flash.
 *
 * Le feedback inclut :
 * - Analyse globale de la performance
 * - Points forts identifiés
 * - Lacunes précises avec explications pédagogiques
 * - Sections du cours à revoir
 * - Plan d'action personnalisé
 * - Encouragements adaptés au niveau
 */
class AiFeedbackService
{
    public function __construct(
        private readonly GeminiApiClient $geminiClient,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Génère un feedback IA personnalisé pour une tentative de quiz.
     *
     * @param QuizAttempts $attempt La tentative de quiz à analyser
     *
     * @return array{
     *     score: float,
     *     passed: bool,
     *     totalQuestions: int,
     *     correctAnswers: int,
     *     wrongAnswers: int,
     *     feedback: string,
     *     strengths: string[],
     *     weaknesses: string[],
     *     sectionsToReview: string[],
     *     actionPlan: string[],
     *     encouragement: string
     * }
     */
    public function generateFeedback(QuizAttempts $attempt): array
    {
        // 1. Collecter les données de la tentative
        $studentResponses = $this->em->getRepository(StudentResponse::class)
            ->findBy(['attempt' => $attempt], ['id' => 'ASC']);

        $quiz = $attempt->getQuiz();
        $course = $quiz->getCourse();
        $student = $attempt->getStudent();

        // 2. Préparer le résumé des réponses
        $responseData = $this->buildResponseData($studentResponses);

        // 3. Récupérer les sections_to_review du cours
        $sectionsToReview = $course->getSectionsToReview() ?? [];

        // 4. Construire le prompt pour Gemini
        $prompt = $this->buildPrompt(
            studentName: $student->getPrenom() ?? 'Étudiant',
            courseName: $course->getTitle(),
            courseDescription: $course->getDescription(),
            courseDifficulty: $course->getDifficulty(),
            quizTitle: $quiz->getTitle(),
            score: $attempt->getScore(),
            passingScore: $quiz->getPassingScore(),
            responseData: $responseData,
            sectionsToReview: $sectionsToReview,
        );

        // 5. Appeler Gemini pour le feedback
        try {
            $aiResponse = $this->geminiClient->generateContent($prompt, temperature: 0.7, maxTokens: 2048);
            $parsed = $this->parseAiResponse($aiResponse);
        } catch (\Exception $e) {
            $this->logger->error('AI Feedback generation failed, using fallback', [
                'attemptId' => $attempt->getId(),
                'error' => $e->getMessage(),
            ]);
            $parsed = $this->buildFallbackFeedback($responseData, $sectionsToReview, $attempt->getScore());
        }

        // 6. Assembler la réponse finale
        return [
            'score' => $attempt->getScore(),
            'passed' => $attempt->getScore() >= ($quiz->getPassingScore() ?? 70),
            'totalQuestions' => count($studentResponses),
            'correctAnswers' => $responseData['correctCount'],
            'wrongAnswers' => $responseData['wrongCount'],
            'feedback' => $parsed['feedback'],
            'strengths' => $parsed['strengths'],
            'weaknesses' => $parsed['weaknesses'],
            'sectionsToReview' => $parsed['sectionsToReview'],
            'actionPlan' => $parsed['actionPlan'],
            'encouragement' => $parsed['encouragement'],
        ];
    }

    /**
     * Prépare les données structurées des réponses de l'étudiant.
     */
    private function buildResponseData(array $studentResponses): array
    {
        $correct = [];
        $wrong = [];

        foreach ($studentResponses as $response) {
            /** @var StudentResponse $response */
            $question = $response->getQuestion();
            $selectedAnswer = $response->getSelectedAnswer();

            $entry = [
                'question' => $question->getContent(),
                'studentAnswer' => $selectedAnswer->getContent(),
                'isCorrect' => $response->isCorrect(),
                'pointsEarned' => $response->getPointsEarned(),
                'maxPoints' => $question->getPoint(),
            ];

            // Trouver la bonne réponse si l'étudiant s'est trompé
            if (!$response->isCorrect()) {
                foreach ($question->getAnswers() as $answer) {
                    if ($answer->isCorrect()) {
                        $entry['correctAnswer'] = $answer->getContent();
                        break;
                    }
                }
                $wrong[] = $entry;
            } else {
                $correct[] = $entry;
            }
        }

        return [
            'correct' => $correct,
            'wrong' => $wrong,
            'correctCount' => count($correct),
            'wrongCount' => count($wrong),
            'total' => count($studentResponses),
        ];
    }

    /**
     * Construit le prompt détaillé pour Gemini 1.5 Flash.
     */
    private function buildPrompt(
        string $studentName,
        string $courseName,
        string $courseDescription,
        string $courseDifficulty,
        string $quizTitle,
        float $score,
        ?float $passingScore,
        array $responseData,
        array $sectionsToReview,
    ): string {
        $passed = $score >= ($passingScore ?? 70);
        $passingScoreText = $passingScore ?? 70;

        // Formatter les réponses incorrectes
        $wrongAnswersText = '';
        foreach ($responseData['wrong'] as $i => $w) {
            $num = $i + 1;
            $wrongAnswersText .= <<<EOT
            
Erreur #{$num}:
- Question : {$w['question']}
- Réponse de l'étudiant : {$w['studentAnswer']}
- Bonne réponse : {$w['correctAnswer']}
- Points perdus : {$w['maxPoints']}
EOT;
        }

        if (empty($wrongAnswersText)) {
            $wrongAnswersText = "Aucune erreur — l'étudiant a tout juste !";
        }

        // Formatter les réponses correctes
        $correctAnswersText = '';
        foreach ($responseData['correct'] as $i => $c) {
            $num = $i + 1;
            $correctAnswersText .= "\n- Réussite #{$num} : {$c['question']}";
        }

        if (empty($correctAnswersText)) {
            $correctAnswersText = "Aucune réponse correcte.";
        }

        // Formatter les sections à revoir
        $sectionsText = !empty($sectionsToReview)
            ? implode("\n- ", array_merge([''], $sectionsToReview))
            : "Aucune section spécifique identifiée.";

        $prompt = <<<PROMPT
Tu es un tuteur pédagogique expert et bienveillant. Tu dois analyser les résultats d'un quiz d'un étudiant et générer un feedback personnalisé, constructif et encourageant.

=== CONTEXTE ===
- Étudiant : {$studentName}
- Cours : {$courseName} (Niveau : {$courseDifficulty})
- Description du cours : {$courseDescription}
- Quiz : {$quizTitle}
- Score obtenu : {$score}% (Score requis : {$passingScoreText}%)
- Résultat : {($passed ? 'RÉUSSI ✅' : 'ÉCHOUÉ ❌')}
- Questions correctes : {$responseData['correctCount']}/{$responseData['total']}

=== RÉPONSES INCORRECTES (à analyser en détail) ===
{$wrongAnswersText}

=== RÉPONSES CORRECTES (points forts) ===
{$correctAnswersText}

=== SECTIONS DU COURS À REVOIR (identifiées par l'enseignant) ===
{$sectionsText}

=== INSTRUCTIONS ===
Génère un feedback pédagogique personnalisé au format JSON strict suivant. Réponds UNIQUEMENT avec le JSON, sans texte avant ni après :

{
  "feedback": "Un paragraphe de 3-4 phrases avec une analyse globale personnalisée de la performance. Mentionne le score, ce qui a été bien fait, et les axes d'amélioration.",
  "strengths": ["Point fort 1 identifié à partir des bonnes réponses", "Point fort 2", "..."],
  "weaknesses": ["Lacune 1 avec explication pédagogique claire de pourquoi la bonne réponse est X", "Lacune 2 avec explication", "..."],
  "sectionsToReview": ["Section/concept 1 à revoir en priorité", "Section 2", "..."],
  "actionPlan": ["Action concrète 1 que l'étudiant devrait faire", "Action 2", "Action 3"],
  "encouragement": "Un message d'encouragement personnalisé et motivant adapté au score obtenu."
}

RÈGLES IMPORTANTES :
1. Sois spécifique aux questions posées, pas générique.
2. Pour chaque erreur, explique POURQUOI la bonne réponse est correcte.
3. Relie les lacunes aux sections du cours à revoir quand c'est possible.
4. Adapte le ton au score : encourageant si faible, félicitant si élevé.
5. Le plan d'action doit être concret et réalisable.
6. Réponds en français.
7. Les tableaux (strengths, weaknesses, etc.) doivent contenir 2-5 éléments chacun.
PROMPT;

        return $prompt;
    }

    /**
     * Parse la réponse JSON de Gemini.
     */
    private function parseAiResponse(string $aiResponse): array
    {
        // Nettoyer la réponse (retirer les éventuels ```json ... ```)
        $cleaned = $aiResponse;
        if (str_contains($cleaned, '```json')) {
            $cleaned = preg_replace('/```json\s*/', '', $cleaned);
            $cleaned = preg_replace('/```\s*$/', '', $cleaned);
        } elseif (str_contains($cleaned, '```')) {
            $cleaned = preg_replace('/```\s*/', '', $cleaned);
        }

        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Failed to parse Gemini JSON response', [
                'error' => json_last_error_msg(),
                'response' => substr($aiResponse, 0, 500),
            ]);

            // Tenter de récupérer le JSON brut avec regex
            if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
                $data = json_decode($matches[0], true);
            }

            if (!$data) {
                // Utiliser la réponse brute comme feedback
                return [
                    'feedback' => $aiResponse,
                    'strengths' => [],
                    'weaknesses' => [],
                    'sectionsToReview' => [],
                    'actionPlan' => [],
                    'encouragement' => 'Continue tes efforts, chaque tentative te rapproche de la réussite ! 💪',
                ];
            }
        }

        return [
            'feedback' => $data['feedback'] ?? '',
            'strengths' => $data['strengths'] ?? [],
            'weaknesses' => $data['weaknesses'] ?? [],
            'sectionsToReview' => $data['sectionsToReview'] ?? [],
            'actionPlan' => $data['actionPlan'] ?? [],
            'encouragement' => $data['encouragement'] ?? 'Continue tes efforts ! 💪',
        ];
    }

    /**
     * Feedback de secours si l'API Gemini est indisponible.
     */
    private function buildFallbackFeedback(array $responseData, array $sectionsToReview, float $score): array
    {
        $strengths = [];
        foreach (array_slice($responseData['correct'], 0, 3) as $c) {
            $strengths[] = "Bonne maîtrise : " . mb_substr($c['question'], 0, 80) . '...';
        }

        $weaknesses = [];
        foreach ($responseData['wrong'] as $w) {
            $weaknesses[] = sprintf(
                'Erreur sur « %s » — La bonne réponse était : %s',
                mb_substr($w['question'], 0, 60),
                $w['correctAnswer'] ?? 'N/A'
            );
        }

        $feedback = sprintf(
            'Vous avez obtenu %s%% avec %d/%d réponses correctes. %s',
            round($score),
            $responseData['correctCount'],
            $responseData['total'],
            $score >= 70
                ? 'Bon travail ! Quelques points restent à consolider.'
                : 'Des efforts supplémentaires sont nécessaires. Revoyez les concepts ci-dessous.'
        );

        return [
            'feedback' => $feedback,
            'strengths' => $strengths ?: ['Vous avez fait l\'effort de passer le quiz, c\'est un bon début !'],
            'weaknesses' => $weaknesses ?: ['Aucune lacune majeure identifiée.'],
            'sectionsToReview' => $sectionsToReview ?: ['Revoyez l\'ensemble du cours.'],
            'actionPlan' => [
                'Relire les sections identifiées comme faibles.',
                'Refaire le quiz après révision.',
                'Consulter les ressources complémentaires du cours.',
            ],
            'encouragement' => $score >= 70
                ? 'Bravo pour cette performance ! Continuez sur cette lancée ! 🎉'
                : 'Ne vous découragez pas ! Chaque erreur est une opportunité d\'apprentissage. 💪',
        ];
    }
}
