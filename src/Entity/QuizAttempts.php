<?php

namespace App\Entity;

use App\Repository\QuizAttemptsRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\User;

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

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
