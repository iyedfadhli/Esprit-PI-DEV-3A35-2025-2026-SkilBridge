<?php

namespace App\Entity;

use App\Repository\StudentResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentResponseRepository::class)]
class StudentResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'studentResponses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?QuizAttempts $attempt = null;

    #[ORM\ManyToOne(inversedBy: 'studentResponses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Question $question = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Answer $selected_answer = null;

    #[ORM\Column]
    private bool $is_correct = false;

    #[ORM\Column]
    private float $points_earned = 0.0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttempt(): ?QuizAttempts
    {
        return $this->attempt;
    }

    public function setAttempt(?QuizAttempts $attempt): static
    {
        $this->attempt = $attempt;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getSelectedAnswer(): ?Answer
    {
        return $this->selected_answer;
    }

    public function setSelectedAnswer(?Answer $selected_answer): static
    {
        $this->selected_answer = $selected_answer;
        return $this;
    }

    public function isCorrect(): bool
    {
        return $this->is_correct;
    }

    public function setIsCorrect(bool $is_correct): static
    {
        $this->is_correct = $is_correct;
        return $this;
    }

    public function getPointsEarned(): float
    {
        return $this->points_earned;
    }

    public function setPointsEarned(float $points_earned): static
    {
        $this->points_earned = $points_earned;
        return $this;
    }
}
