<?php

namespace App\Controller\Api;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizAttempts;
use App\Entity\StudentResponse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur API pour le système de timer de quiz sécurisé.
 *
 * Sécurité :
 * - `startedAt` est TOUJOURS généré côté serveur (jamais accepté du frontend)
 * - La validation du temps est faite côté serveur uniquement
 * - Grace period de 10 secondes pour la latence réseau
 *
 * Endpoints :
 * - POST /api/quizzes/{id}/start-attempt     → Démarrer une tentative
 * - POST /api/quizzes/{id}/attempts/{attemptId}/submit → Soumettre avec validation temps
 * - GET  /api/quizzes/attempts/{attemptId}/status      → Statut (pour reconnexion)
 */
class QuizTimerController extends AbstractController
{
    /**
     * Tolérance réseau en secondes.
     * Permet de compenser la latence entre le frontend et le backend.
     * Une soumission à 61s pour un quiz de 60s sera acceptée.
     */
    private const GRACE_PERIOD_SECONDS = 10;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    // ════════════════════════════════════════════════════════════════
    // ENDPOINT 1 : Démarrer une tentative
    // ════════════════════════════════════════════════════════════════

    /**
     * Démarre une nouvelle tentative de quiz.
     *
     * - Vérifie que le quiz existe et que le nombre max de tentatives n'est pas atteint
     * - Enregistre `startedAt = now()` UNIQUEMENT côté serveur
     * - Retourne les questions mélangées + le temps limite en secondes
     *
     * @return JsonResponse
     *   Succès 201 : { attempt_id, time_limit_seconds, questions_count, started_at, questions[] }
     *   Erreur 404 : Quiz non trouvé
     *   Erreur 403 : Nombre max de tentatives atteint
     *   Erreur 409 : Tentative déjà en cours
     */
    #[Route('/api/quizzes/{id}/start-attempt', name: 'api_quiz_start_attempt', methods: ['POST'])]
    public function startAttempt(int $id): JsonResponse
    {
        // ── Récupérer le quiz ──
        $quiz = $this->em->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz non trouvé'], 404);
        }

        // ── Récupérer l'étudiant ──
        $student = $this->getUser();
        if (!$student) {
            // Fallback dev : prendre le premier utilisateur en base
            $student = $this->em->getRepository(User::class)->findOneBy([]);
        }
        if (!$student) {
            return new JsonResponse(['error' => 'Aucun utilisateur trouvé'], 404);
        }

        $attemptRepo = $this->em->getRepository(QuizAttempts::class);

        // ── Vérifier s'il y a une tentative IN_PROGRESS existante ──
        $existingInProgress = $attemptRepo->findOneBy([
            'quiz'    => $quiz,
            'student' => $student,
            'status'  => 'IN_PROGRESS',
        ]);

        if ($existingInProgress) {
            // If startedAt was not set yet (page loaded but "Start" never clicked),
            // set it NOW so the timer begins from this moment.
            if ($existingInProgress->getStartedAt() === null) {
                $existingInProgress->setStartedAt(new \DateTimeImmutable());
                $this->em->flush();
            }

            // Retourner la tentative existante plutôt que d'en créer une nouvelle
            $remainingSeconds = $existingInProgress->getRemainingSeconds();
            $timeLimit = $quiz->getTimeLimitSeconds();

            // Si le temps est expiré pour cette tentative, la marquer EXPIRED
            if ($timeLimit > 0 && $remainingSeconds !== null && $remainingSeconds <= 0) {
                $existingInProgress->setStatus('EXPIRED');
                $existingInProgress->setSubmittedAt(new \DateTimeImmutable());
                $this->em->flush();

                return new JsonResponse([
                    'error'   => 'Tentative précédente expirée',
                    'status'  => 'EXPIRED',
                    'message' => 'Votre tentative précédente a expiré. Vous pouvez en démarrer une nouvelle.',
                ], 410);
            }

            // Retourner la tentative en cours avec les questions
            $questions = $this->getShuffledQuestions($quiz);

            return new JsonResponse([
                'attempt_id'         => $existingInProgress->getId(),
                'time_limit_seconds' => $timeLimit,
                'remaining_seconds'  => $remainingSeconds,
                'questions_count'    => count($questions),
                'started_at'         => $existingInProgress->getStartedAt()->format('c'),
                'status'             => 'IN_PROGRESS',
                'resumed'            => true,
                'questions'          => $questions,
            ], 200);
        }

        // ── Vérifier le nombre max de tentatives ──
        $attemptCount = $attemptRepo->count([
            'quiz'    => $quiz,
            'student' => $student,
        ]);

        if ($attemptCount >= $quiz->getMaxAttempts()) {
            return new JsonResponse([
                'error'        => 'Nombre maximum de tentatives atteint',
                'max_attempts' => $quiz->getMaxAttempts(),
                'used'         => $attemptCount,
            ], 403);
        }

        // ── Créer la tentative ──
        $attempt = new QuizAttempts();
        $attempt->setQuiz($quiz);
        $attempt->setStudent($student);
        $attempt->setAttemptNbr($attemptCount + 1);
        $attempt->setScore(0);
        $attempt->setSubmittedAt(new \DateTimeImmutable());
        $attempt->setStatus('IN_PROGRESS');

        // ╔══════════════════════════════════════════════════════════╗
        // ║  SÉCURITÉ : startedAt est TOUJOURS généré par le       ║
        // ║  serveur. JAMAIS accepté depuis le frontend.           ║
        // ╚══════════════════════════════════════════════════════════╝
        $attempt->setStartedAt(new \DateTimeImmutable());

        $this->em->persist($attempt);
        $this->em->flush();

        // ── Préparer les questions ──
        $questions = $this->getShuffledQuestions($quiz);
        $timeLimit = $quiz->getTimeLimitSeconds();

        return new JsonResponse([
            'attempt_id'         => $attempt->getId(),
            'time_limit_seconds' => $timeLimit,
            'remaining_seconds'  => $timeLimit > 0 ? $timeLimit : null,
            'questions_count'    => count($questions),
            'started_at'         => $attempt->getStartedAt()->format('c'),
            'status'             => 'IN_PROGRESS',
            'resumed'            => false,
            'questions'          => $questions,
        ], 201);
    }

    // ════════════════════════════════════════════════════════════════
    // ENDPOINT 2 : Soumettre une tentative avec validation temps
    // ════════════════════════════════════════════════════════════════

    /**
     * Soumet les réponses d'une tentative avec validation stricte du temps côté serveur.
     *
     * Processus :
     * 1. Vérifie que la tentative existe et est IN_PROGRESS
     * 2. Calcule le temps écoulé : now() - startedAt (SERVEUR uniquement)
     * 3. Compare avec timeLimit * 60 + GRACE_PERIOD (10s)
     * 4. Si dépassement → statut EXPIRED, réponses sauvegardées, erreur 400
     * 5. Si dans les temps → statut SUBMITTED, score calculé, succès 200
     *
     * Body JSON attendu :
     *   { "responses": { "questionId": "answerId", ... } }
     *
     * @return JsonResponse
     *   Succès 200 : { attempt_id, score, status: "SUBMITTED", ... }
     *   Erreur 400 : { error, allowed_seconds, elapsed_seconds, status: "EXPIRED" }
     *   Erreur 404 : Tentative non trouvée
     *   Erreur 409 : Tentative déjà soumise
     */
    #[Route('/api/quizzes/{id}/attempts/{attemptId}/submit', name: 'api_quiz_submit_timed', methods: ['POST'])]
    public function submitAttempt(int $id, int $attemptId, Request $request): JsonResponse
    {
        // ── Récupérer le quiz et la tentative ──
        $quiz = $this->em->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz non trouvé'], 404);
        }

        $attempt = $this->em->getRepository(QuizAttempts::class)->find($attemptId);
        if (!$attempt || $attempt->getQuiz()?->getId() !== $quiz->getId()) {
            return new JsonResponse(['error' => 'Tentative non trouvée pour ce quiz'], 404);
        }

        // ── Vérifier que la tentative est bien IN_PROGRESS ──
        if ($attempt->getStatus() !== 'IN_PROGRESS') {
            return new JsonResponse([
                'error'  => 'Cette tentative a déjà été soumise ou a expiré',
                'status' => $attempt->getStatus(),
            ], 409);
        }

        // ── Décoder le payload ──
        $data = json_decode($request->getContent(), true);
        $responses = $data['responses'] ?? [];

        // ════════════════════════════════════════════════════════════
        // SÉCURITÉ : Validation du temps CÔTÉ SERVEUR UNIQUEMENT
        // Le frontend n'envoie AUCUNE information de temps.
        // Calcul : elapsed = now() - startedAt (DateTimeImmutable serveur)
        // ════════════════════════════════════════════════════════════

        $timeLimit = $quiz->getTimeLimitSeconds(); // 0 = pas de limite
        $isExpired = false;
        $elapsedSeconds = $attempt->getElapsedSeconds() ?? 0;

        if ($timeLimit > 0) {
            // Grace period : 10 secondes de tolérance pour la latence réseau
            $allowedSeconds = $timeLimit + self::GRACE_PERIOD_SECONDS;

            if ($elapsedSeconds > $allowedSeconds) {
                $isExpired = true;
            }
        }

        // ── Sauvegarder les réponses JSON brutes (TOUJOURS, même si expiré) ──
        // Cela permet l'audit et la traçabilité même en cas d'expiration
        $attempt->setAnswersJson($responses);

        // ── Traiter les réponses et calculer le score ──
        $scoreResult = $this->processResponses($attempt, $responses);

        // ── Mettre à jour la tentative ──
        $attempt->setScore($scoreResult['percent']);
        $attempt->setSubmittedAt(new \DateTimeImmutable());

        if ($isExpired) {
            // ╔══════════════════════════════════════════════════════╗
            // ║  TEMPS DÉPASSÉ : réponses sauvegardées mais        ║
            // ║  tentative marquée EXPIRED, score non validé       ║
            // ╚══════════════════════════════════════════════════════╝
            $attempt->setStatus('EXPIRED');
            $this->em->flush();

            return new JsonResponse([
                'error'           => 'Temps limite dépassé',
                'allowed_seconds' => $timeLimit,
                'elapsed_seconds' => $elapsedSeconds,
                'grace_period'    => self::GRACE_PERIOD_SECONDS,
                'status'          => 'EXPIRED',
                'attempt_id'      => $attempt->getId(),
                'score'           => $scoreResult['percent'],
                'message'         => sprintf(
                    'Le temps imparti était de %d secondes (+ %ds tolérance). Vous avez mis %d secondes.',
                    $timeLimit,
                    self::GRACE_PERIOD_SECONDS,
                    $elapsedSeconds
                ),
            ], 400);
        }

        // ── Soumission valide ──
        $attempt->setStatus('SUBMITTED');
        $this->em->flush();

        return new JsonResponse([
            'attempt_id'      => $attempt->getId(),
            'score'           => $scoreResult['percent'],
            'correct_count'   => $scoreResult['correctCount'],
            'total_questions' => $scoreResult['totalQuestions'],
            'earned_points'   => $scoreResult['earnedPoints'],
            'total_points'    => $scoreResult['totalPoints'],
            'passed'          => $scoreResult['percent'] >= ($quiz->getPassingScore() ?? 70),
            'elapsed_seconds' => $elapsedSeconds,
            'time_limit'      => $timeLimit,
            'status'          => 'SUBMITTED',
        ], 200);
    }

    // ════════════════════════════════════════════════════════════════
    // ENDPOINT 3 : Statut d'une tentative (pour reconnexion)
    // ════════════════════════════════════════════════════════════════

    /**
     * Retourne le statut actuel d'une tentative.
     * Utilisé pour la reprise après déconnexion réseau.
     *
     * @return JsonResponse
     *   { attempt_id, status, remaining_seconds, elapsed_seconds, time_limit_seconds, started_at }
     */
    #[Route('/api/quizzes/attempts/{attemptId}/status', name: 'api_quiz_attempt_status', methods: ['GET'])]
    public function attemptStatus(int $attemptId): JsonResponse
    {
        $attempt = $this->em->getRepository(QuizAttempts::class)->find($attemptId);
        if (!$attempt) {
            return new JsonResponse(['error' => 'Tentative non trouvée'], 404);
        }

        $quiz = $attempt->getQuiz();
        $timeLimit = $quiz ? $quiz->getTimeLimitSeconds() : 0;
        $elapsedSeconds = $attempt->getElapsedSeconds();
        $remainingSeconds = $attempt->getRemainingSeconds();

        // Si la tentative est IN_PROGRESS mais le temps est dépassé, la marquer EXPIRED
        if (
            $attempt->getStatus() === 'IN_PROGRESS'
            && $timeLimit > 0
            && $elapsedSeconds !== null
            && $elapsedSeconds > ($timeLimit + self::GRACE_PERIOD_SECONDS)
        ) {
            $attempt->setStatus('EXPIRED');
            $attempt->setSubmittedAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        return new JsonResponse([
            'attempt_id'         => $attempt->getId(),
            'status'             => $attempt->getStatus(),
            'remaining_seconds'  => $remainingSeconds,
            'elapsed_seconds'    => $elapsedSeconds,
            'time_limit_seconds' => $timeLimit,
            'started_at'         => $attempt->getStartedAt()?->format('c'),
            'score'              => $attempt->getScore(),
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ════════════════════════════════════════════════════════════════

    /**
     * Récupère les questions d'un quiz, mélangées, avec réponses mélangées.
     */
    private function getShuffledQuestions(Quiz $quiz): array
    {
        $questions = $this->em->getRepository(Question::class)->findBy(['quiz' => $quiz]);

        shuffle($questions);
        $limit = $quiz->getQuestionsPerAttempt() ?? count($questions);
        $questions = array_slice($questions, 0, $limit);

        $out = [];
        foreach ($questions as $q) {
            $answers = [];
            foreach ($q->getAnswers() as $a) {
                $answers[] = ['id' => $a->getId(), 'text' => $a->getContent()];
            }
            shuffle($answers);
            $out[] = [
                'id'      => $q->getId(),
                'text'    => $q->getContent(),
                'type'    => $q->getType(),
                'points'  => $q->getPoint(),
                'answers' => $answers,
            ];
        }

        return $out;
    }

    /**
     * Traite les réponses soumises et crée les entités StudentResponse.
     *
     * Le score est calculé sur TOUTES les questions du quiz (limitées par
     * questionsPerAttempt). Les questions non répondues comptent comme
     * des réponses fausses (0 points).
     *
     * @param QuizAttempts $attempt     La tentative
     * @param array<int|string, int|string> $responses   Map { questionId => answerId }
     * @return array{ percent: float, correctCount: int, totalQuestions: int, earnedPoints: float, totalPoints: float }
     */
    private function processResponses(QuizAttempts $attempt, array $responses): array
    {
        $quiz = $attempt->getQuiz();

        // ── Récupérer TOUTES les questions du quiz ──
        $allQuestions = $this->em->getRepository(Question::class)->findBy(['quiz' => $quiz]);
        $limit = $quiz->getQuestionsPerAttempt() ?? count($allQuestions);
        // On ne tronque que si les questions envoyées ne couvrent pas tout :
        // on prend le max entre le nombre de questions envoyées et la limite
        // configurée pour s'assurer de couvrir toutes les questions assignées.
        $totalQuestions = max(count($responses), min($limit, count($allQuestions)));

        // ── Calculer les points totaux sur TOUTES les questions assignées ──
        // Trier pour prendre les mêmes N questions de manière déterministe
        $totalPoints = 0.0;
        $questionsById = [];
        foreach ($allQuestions as $q) {
            $questionsById[$q->getId()] = $q;
            $totalPoints += $q->getPoint();
        }
        // Si questionsPerAttempt < total questions, ajuster totalPoints
        // au prorata (les questions réellement assignées).
        if ($limit < count($allQuestions)) {
            // On recalcule totalPoints à partir des questions qui ont été
            // effectivement envoyées + celles non répondues parmi la limite.
            // Le plus fiable : prendre les points des questions pour
            // lesquelles on a une réponse + compléter avec les autres.
            $assignedIds = array_keys($responses);
            $remainingQuestions = array_filter($allQuestions, fn($q) => !isset($responses[$q->getId()]));
            $slotsLeft = $limit - count($assignedIds);
            $extraIds = [];
            foreach ($remainingQuestions as $rq) {
                if ($slotsLeft <= 0) break;
                $extraIds[] = $rq->getId();
                $slotsLeft--;
            }
            $allAssignedIds = array_merge($assignedIds, $extraIds);
            $totalPoints = 0.0;
            foreach ($allAssignedIds as $qid) {
                if (isset($questionsById[$qid])) {
                    $totalPoints += $questionsById[$qid]->getPoint();
                }
            }
            $totalQuestions = count($allAssignedIds);
        }

        // ── Traiter les réponses soumises ──
        $earnedPoints = 0.0;
        $correctCount = 0;

        foreach ($responses as $questionId => $answerId) {
            $question = $this->em->getRepository(Question::class)->find($questionId);
            $answer = $this->em->getRepository(Answer::class)->find($answerId);

            if (!$question || !$answer) {
                continue;
            }

            $isCorrect = $answer->isCorrect();
            $pointsEarned = $isCorrect ? $question->getPoint() : 0;

            if ($isCorrect) {
                $correctCount++;
                $earnedPoints += $pointsEarned;
            }

            // Créer StudentResponse pour audit
            $studentResponse = new StudentResponse();
            $studentResponse->setAttempt($attempt);
            $studentResponse->setQuestion($question);
            $studentResponse->setSelectedAnswer($answer);
            $studentResponse->setIsCorrect($isCorrect);
            $studentResponse->setPointsEarned($pointsEarned);
            $this->em->persist($studentResponse);
        }

        // Le pourcentage est calculé sur le TOTAL des points possibles
        // (toutes les questions assignées), pas seulement celles répondues.
        $percent = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        return [
            'percent'        => $percent,
            'correctCount'   => $correctCount,
            'totalQuestions'  => $totalQuestions,
            'earnedPoints'   => $earnedPoints,
            'totalPoints'    => $totalPoints,
        ];
    }
}
