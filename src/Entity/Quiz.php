<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Course;
use App\Entity\Chapter;
use App\Entity\Question;
use App\Entity\QuizAttempts;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Course $course = null;

    #[ORM\OneToOne(inversedBy: 'quiz')]
    #[ORM\JoinColumn(nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?Chapter $chapter = null;

    #[ORM\Column(length: 30)]
    private ?string $title = null;

    #[ORM\Column]
    private ?float $passing_score = null;

    #[ORM\Column]
    private ?int $max_attempts = null;

    #[ORM\Column(nullable: true)]
    private ?int $questions_per_attempt = null;

    /**
     * Durée limite du quiz en minutes.
     * 0 = pas de limite de temps (quiz sans chrono).
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $time_limit = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $supervisor = null;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: QuizAttempts::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $quizAttempts;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->quizAttempts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPassingScore(): ?float
    {
        return $this->passing_score;
    }

    public function setPassingScore(float $passing_score): static
    {
        $this->passing_score = $passing_score;

        return $this;
    }

    public function getMaxAttempts(): ?int
    {
        return $this->max_attempts;
    }

    public function setMaxAttempts(int $max_attempts): static
    {
        $this->max_attempts = $max_attempts;

        return $this;
    }

    public function getQuestionsPerAttempt(): ?int
    {
        return $this->questions_per_attempt;
    }

    public function setQuestionsPerAttempt(?int $questions_per_attempt): static
    {
        $this->questions_per_attempt = $questions_per_attempt;

        return $this;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): static
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @return Collection<int, QuizAttempts>
     */
    public function getQuizAttempts(): Collection
    {
        return $this->quizAttempts;
    }

    /**
     * @return int Durée limite en minutes (0 = illimité)
     */
    public function getTimeLimit(): int
    {
        return $this->time_limit;
    }

    /**
     * @param int $timeLimit Durée en minutes (0 = désactivé)
     */
    public function setTimeLimit(int $timeLimit): static
    {
        $this->time_limit = $timeLimit;
        return $this;
    }

    /**
     * @return int Durée limite convertie en secondes (0 = illimité)
     */
    public function getTimeLimitSeconds(): int
    {
        return $this->time_limit * 60;
    }

    public function __toString(): string
    {
        $chapterTitle = $this->chapter?->getTitle() ?? '';
        $title = $this->title ?? '';
        if ($chapterTitle !== '') {
            return $chapterTitle.' - '.($title !== '' ? $title : 'Quiz #'.$this->id);
        }

        return $title !== '' ? $title : 'Quiz #'.$this->id;
    }
}
