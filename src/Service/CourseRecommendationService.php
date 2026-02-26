<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\CourseRepository;
use App\Repository\EnrollementRepository;
use App\Repository\QuizAttemptsRepository;

/**
 * Service de recommandation intelligente de cours.
 *
 * Propose les 3 prochains cours les plus pertinents à un étudiant
 * en se basant sur :
 * - Les prérequis validés (enrollement COMPLETED du cours prérequis)
 * - Le niveau actuel de l'étudiant (difficulté max des cours complétés)
 * - La performance récente (score moyen des 3 derniers quiz réussis)
 * - La progression naturelle (éviter les sauts de difficulté)
 */
class CourseRecommendationService
{
    /** Nombre maximum de cours recommandés à retourner */
    private const MAX_RECOMMENDATIONS = 3;

    /** Badges visuels selon le score de priorité */
    private const BADGES = [
        'high'   => 'Prochaine étape logique ⬆️',
        'medium' => 'Parfait pour vous 🔥',
        'low'    => 'Recommandé ⭐',
    ];

    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly EnrollementRepository $enrollementRepository,
        private readonly QuizAttemptsRepository $quizAttemptsRepository,
    ) {
    }

    /**
     * Génère les recommandations de cours pour un étudiant.
     *
     * @param User $student L'utilisateur connecté
     * @return array Tableau structuré avec les cours recommandés
     */
    public function getRecommendations(User $student): array
    {
        $studentId = $student->getId();

        // ── 1. Récupérer les données nécessaires en un minimum de requêtes ──

        // Cours complétés par l'étudiant (avec JOIN sur course)
        $completedEnrolments = $this->enrollementRepository->findCompletedByStudent($studentId);

        // IDs des cours COMPLETED → à exclure des recommandations
        $completedCourseIds = [];
        foreach ($completedEnrolments as $enrolment) {
            $completedCourseIds[] = $enrolment->getCourse()->getId();
        }

        // IDs des cours inscrits (tous statuts) → pour les prérequis
        $enrolledCourseIds = $this->enrollementRepository->findEnrolledCourseIdsByStudent($studentId);

        // Tous les cours actifs avec prérequis (JOIN optimisé)
        $activeCourses = $this->courseRepository->findActiveCoursesWithPrerequisites();

        // Les 3 derniers quiz réussis → score moyen récent
        $lastPassedAttempts = $this->quizAttemptsRepository->findLastPassedAttempts($studentId);

        // ── 2. Calculer le niveau actuel de l'étudiant ──
        // On utilise aussi les quiz réussis pour évaluer le niveau,
        // pas uniquement les enrollments COMPLETED (qui sont mis à jour en lazy).
        $studentCurrentLevel = $this->calculateStudentLevel($completedEnrolments);

        // Si le niveau est 0 mais que l'étudiant a réussi des quiz,
        // on estime le niveau à partir de la difficulté des cours des quiz réussis.
        if ($studentCurrentLevel === 0 && !empty($lastPassedAttempts)) {
            foreach ($lastPassedAttempts as $attempt) {
                $quiz = $attempt->getQuiz();
                if ($quiz && $quiz->getCourse()) {
                    $courseLevel = $quiz->getCourse()->getDifficultyLevel();
                    $studentCurrentLevel = max($studentCurrentLevel, $courseLevel);
                }
            }
        }

        $recentAvgScore = $this->calculateRecentAvgScore($lastPassedAttempts);

        // ── 3. Filtrer et scorer les cours éligibles ──
        // On inclut :
        //   - Les cours non inscrits (si prérequis validé)
        //   - Les cours IN_PROGRESS (pas encore terminés)
        // On exclut :
        //   - Les cours déjà COMPLETED

        $candidates = [];

        foreach ($activeCourses as $course) {
            // Exclure les cours déjà COMPLETED (pas besoin de recommander)
            if (in_array($course->getId(), $completedCourseIds, true)) {
                continue;
            }

            // Pour les cours non inscrits, vérifier la validation du prérequis
            $isEnrolled = in_array($course->getId(), $enrolledCourseIds, true);
            if (!$isEnrolled && !$this->isPrerequisiteMet($course, $completedCourseIds)) {
                continue;
            }

            // Calculer le score de priorité et la raison
            $scoringResult = $this->calculatePriorityScore(
                $course,
                $studentCurrentLevel,
                $recentAvgScore,
                $completedCourseIds
            );

            $candidates[] = [
                'course'         => $course,
                'priority_score' => $scoringResult['score'],
                'reason'         => $scoringResult['reason'],
                'enrolled'       => $isEnrolled,
            ];
        }

        // ── 5. Trier par score de priorité décroissant ──

        usort($candidates, fn($a, $b) => $b['priority_score'] <=> $a['priority_score']);

        // ── 6. Limiter à MAX_RECOMMENDATIONS résultats ──

        $candidates = array_slice($candidates, 0, self::MAX_RECOMMENDATIONS);

        // ── 7. Formater la réponse JSON ──

        return $this->formatResponse($candidates, $studentCurrentLevel, $recentAvgScore);
    }

    /**
     * Calcule le niveau actuel de l'étudiant.
     * Correspond à la difficulté la plus élevée parmi ses cours COMPLETED.
     *
     * @param array $completedEnrolments Les inscritions complétées
     * @return int Niveau numérique (0 si aucun cours complété)
     */
    private function calculateStudentLevel(array $completedEnrolments): int
    {
        $maxLevel = 0;

        foreach ($completedEnrolments as $enrolment) {
            $course = $enrolment->getCourse();
            $level = Course::DIFFICULTY_LEVELS[$course->getDifficulty()] ?? 1;
            $maxLevel = max($maxLevel, $level);
        }

        return $maxLevel;
    }

    /**
     * Calcule le score moyen des 3 derniers quiz réussis.
     *
     * @param array $lastPassedAttempts Les tentatives réussies récentes
     * @return float Score moyen (0.0 si aucune tentative)
     */
    private function calculateRecentAvgScore(array $lastPassedAttempts): float
    {
        if (empty($lastPassedAttempts)) {
            return 0.0;
        }

        $totalScore = 0.0;
        foreach ($lastPassedAttempts as $attempt) {
            $totalScore += $attempt->getScore();
        }

        return round($totalScore / count($lastPassedAttempts), 2);
    }

    /**
     * Vérifie si le prérequis d'un cours est satisfait.
     *
     * Un cours est éligible si :
     * - Il n'a pas de prérequis quiz (prerequisite_quiz = null)
     * - OU le cours auquel appartient le quiz prérequis est COMPLETED
     *
     * @param Course $course Le cours à vérifier
     * @param int[]  $completedCourseIds Les IDs des cours complétés par l'étudiant
     * @return bool true si le prérequis est validé
     */
    private function isPrerequisiteMet(Course $course, array $completedCourseIds): bool
    {
        $prerequisiteQuiz = $course->getPrerequisiteQuiz();

        // Pas de prérequis → toujours éligible
        if ($prerequisiteQuiz === null) {
            return true;
        }

        // Le cours parent du quiz prérequis doit être COMPLETED
        $prerequisiteCourse = $prerequisiteQuiz->getCourse();
        if ($prerequisiteCourse === null) {
            return true;
        }

        return in_array($prerequisiteCourse->getId(), $completedCourseIds, true);
    }

    /**
     * Calcule le score de priorité d'un cours candidat (0-100).
     *
     * Critères de scoring :
     * 1. Difficulté immédiatement supérieure au niveau actuel (+40 pts)
     * 2. Même niveau que l'étudiant (+25 pts)
     * 3. Score moyen récent élevé → bonus progressif (+0 à 30 pts)
     * 4. Cours récent (ID élevé) → léger bonus (+0 à 10 pts)
     * 5. Prérequis direct validé → bonus (+20 pts)
     *
     * @return array{score: int, reason: string}
     */
    private function calculatePriorityScore(
        Course $course,
        int $studentCurrentLevel,
        float $recentAvgScore,
        array $completedCourseIds
    ): array {
        $score = 0;
        $reasons = [];
        $courseLevel = $course->getDifficultyLevel();
        $nextLevel = $studentCurrentLevel + 1;

        // ── Critère 1 : Difficulté immédiatement supérieure ──
        if ($courseLevel === $nextLevel) {
            $score += 40;
            $reasons[] = 'niveau suivant logique';
        } elseif ($courseLevel === $studentCurrentLevel && $studentCurrentLevel > 0) {
            // Même niveau → bon pour consolider
            $score += 25;
            $reasons[] = 'consolidation du niveau actuel';
        } elseif ($courseLevel < $studentCurrentLevel) {
            // Niveau inférieur → moins pertinent
            $score += 10;
            $reasons[] = 'niveau inférieur (révision)';
        } else {
            // Niveau trop avancé (saut de +2 ou plus)
            $score += 5;
            $reasons[] = 'niveau avancé disponible';
        }

        // ── Critère 2 : Performance récente de l'étudiant ──
        if ($recentAvgScore > 0) {
            // Bonus proportionnel au score moyen (max 30 pts pour 100%)
            $scoreBonus = (int) round(($recentAvgScore / 100) * 30);
            $score += $scoreBonus;

            if ($recentAvgScore >= 80) {
                $reasons[] = sprintf('forte progression récente (%.0f%%)', $recentAvgScore);
            }
        }

        // ── Critère 3 : Prérequis direct validé ──
        $prerequisiteQuiz = $course->getPrerequisiteQuiz();
        if ($prerequisiteQuiz !== null) {
            $prereqCourse = $prerequisiteQuiz->getCourse();
            if ($prereqCourse !== null && in_array($prereqCourse->getId(), $completedCourseIds, true)) {
                $score += 20;
                $reasons[] = sprintf("Prérequis '%s' validé", $prereqCourse->getTitle());
            }
        }

        // ── Critère 4 : Récence du cours (léger bonus pour les cours récents) ──
        // Normalisation basée sur l'ID (proxy de la date de création)
        $recencyBonus = min(10, (int) round($course->getId() / 10));
        $score += $recencyBonus;

        // Construire la raison finale
        $reason = implode(' + ', $reasons);

        return [
            'score'  => min(100, $score),
            'reason' => $reason,
        ];
    }

    /**
     * Formate la réponse JSON finale.
     *
     * @param array $candidates Les cours candidats triés
     * @param int   $studentLevel Le niveau actuel de l'étudiant
     * @param float $recentAvgScore Le score moyen récent
     * @return array Réponse structurée pour le JSON
     */
    private function formatResponse(array $candidates, int $studentLevel, float $recentAvgScore): array
    {
        $recommendedCourses = [];

        foreach ($candidates as $candidate) {
            /** @var Course $course */
            $course = $candidate['course'];
            $priorityScore = $candidate['priority_score'];

            // Attribuer un badge selon le score de priorité
            $badge = match (true) {
                $priorityScore >= 80 => self::BADGES['high'],
                $priorityScore >= 50 => self::BADGES['medium'],
                default              => self::BADGES['low'],
            };

            // Formater la durée estimée en heures
            $durationHours = $course->getDuration();
            $estimatedDuration = $durationHours . ' heure' . ($durationHours > 1 ? 's' : '');

            $recommendedCourses[] = [
                'id'                 => $course->getId(),
                'title'              => $course->getTitle(),
                'difficulty'         => $course->getDifficulty(),
                'description'        => $course->getDescription(),
                'thumbnail'          => $course->getMaterial(),
                'reason'             => $candidate['reason'],
                'estimated_duration' => $estimatedDuration,
                'priority_score'     => $priorityScore,
                'badge'              => $badge,
                'enrolled'           => $candidate['enrolled'] ?? false,
            ];
        }

        // Construire le message contextuel
        $count = count($recommendedCourses);
        $message = match (true) {
            $count === 0 => 'Aucun cours recommandé pour le moment. Complétez vos cours actuels pour débloquer de nouvelles recommandations !',
            $count === 1 => 'Voici votre prochain cours recommandé selon votre progression !',
            default      => sprintf('Voici vos %d prochains cours recommandés selon votre progression !', $count),
        };

        return [
            'recommended_courses' => $recommendedCourses,
            'message'             => $message,
            'has_more'            => count($recommendedCourses) === self::MAX_RECOMMENDATIONS,
            'meta'                => [
                'student_current_level' => $this->levelToString($studentLevel),
                'recent_avg_score'      => $recentAvgScore,
                'total_candidates'      => $count,
            ],
        ];
    }

    /**
     * Convertit un niveau numérique en libellé lisible.
     */
    private function levelToString(int $level): string
    {
        return match ($level) {
            1       => 'BEGINNER',
            2       => 'INTERMEDIATE',
            3       => 'ADVANCED',
            default => 'NONE',
        };
    }
}
