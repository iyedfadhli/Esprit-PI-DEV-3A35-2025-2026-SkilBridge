<?php

namespace App\Entity;

use App\Repository\QuizAttemptsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\StudentResponse;

#[ORM\Entity(repositoryClass: QuizAttemptsRepository::class)]
class QuizAttempts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $attempt_nbr = null;

    #[ORM\Column]
    private ?float $score = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $submitted_at = null;

    /**
     * Heure de début de la tentative — enregistrée UNIQUEMENT par le serveur.
     * NE JAMAIS accepter cette valeur depuis le frontend.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $started_at = null;

    /**
     * Statut de la tentative :
     * - IN_PROGRESS : quiz en cours
     * - SUBMITTED   : soumis dans les temps
     * - EXPIRED     : temps dépassé (réponses sauvegardées pour audit)
     */
    #[ORM\Column(length: 20, options: ['default' => 'IN_PROGRESS'])]
    private string $status = 'IN_PROGRESS';

    /**
     * Sauvegarde JSON brute des réponses (pour audit, même en cas d'expiration).
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $answers_json = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $student = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Quiz $quiz = null;

    #[ORM\OneToMany(mappedBy: 'attempt', targetEntity: StudentResponse::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $studentResponses;

    public function __construct()
    {
        $this->studentResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttemptNbr(): ?int
    {
        return $this->attempt_nbr;
    }

    public function setAttemptNbr(int $attempt_nbr): static
    {
        $this->attempt_nbr = $attempt_nbr;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submitted_at;
    }

    public function setSubmittedAt(\DateTimeImmutable $submitted_at): static
    {
        $this->submitted_at = $submitted_at;

        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    // ── startedAt ──

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    /**
     * Enregistre l'heure de début — appelé UNIQUEMENT côté serveur.
     */
    public function setStartedAt(?\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;
        return $this;
    }

    // ── status ──

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    // ── answersJson ──

    public function getAnswersJson(): ?array
    {
        return $this->answers_json;
    }

    public function setAnswersJson(?array $answers_json): static
    {
        $this->answers_json = $answers_json;
        return $this;
    }

    /**
     * Calcule les secondes écoulées depuis le début de la tentative.
     */
    public function getElapsedSeconds(): ?int
    {
        if ($this->started_at === null) {
            return null;
        }
        return (new \DateTimeImmutable())->getTimestamp() - $this->started_at->getTimestamp();
    }

    /**
     * Calcule les secondes restantes pour cette tentative.
     * Retourne null si pas de limite de temps ou pas démarré.
     */
    public function getRemainingSeconds(): ?int
    {
        if ($this->started_at === null || $this->quiz === null) {
            return null;
        }
        $timeLimit = $this->quiz->getTimeLimitSeconds();
        if ($timeLimit === 0) {
            return null; // Pas de limite
        }
        $elapsed = $this->getElapsedSeconds();
        return max(0, $timeLimit - $elapsed);
    }

    // helper for admin display: Student name + email
    public function getStudentDisplay(): string
    {
        if ($this->student instanceof User) {
            return trim(($this->student->getNom() ?? '').' '.($this->student->getPrenom() ?? '')).' (' . ($this->student->getEmail() ?? '') . ')';
        }
        return '—';
    }

    // helper for admin: quiz -> course title
    public function getQuizCourseTitle(): string
    {
        if ($this->quiz instanceof Quiz && $this->quiz->getCourse()) {
            return $this->quiz->getCourse()->getTitle();
        }
        return (string) ($this->quiz?->getTitle() ?? '—');
    }
}
