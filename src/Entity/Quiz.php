<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?course $course = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?chapter $chapter = null;

    #[ORM\Column(length: 30)]
    private ?string $title = null;

    #[ORM\Column]
    private ?float $passing_score = null;

    #[ORM\Column]
    private ?int $max_attempts = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $supervisor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?course
    {
        return $this->course;
    }

    public function setCourse(course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getChapter(): ?chapter
    {
        return $this->chapter;
    }

    public function setChapter(chapter $chapter): static
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

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): static
    {
        $this->supervisor = $supervisor;

        return $this;
    }
}
