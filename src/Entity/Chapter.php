<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Course;
use App\Entity\Quiz;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column]
    private ?int $chapter_order = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $min_score = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 30)]
    private ?string $title = null;

    #[ORM\OneToOne(mappedBy: 'chapter', targetEntity: Quiz::class, cascade: ['persist', 'remove'])]
    private ?Quiz $quiz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getChapterOrder(): ?int
    {
        return $this->chapter_order;
    }

    public function setChapterOrder(int $chapter_order): static
    {
        $this->chapter_order = $chapter_order;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMinScore(): ?float
    {
        return $this->min_score;
    }

    public function setMinScore(float $min_score): static
    {
        $this->min_score = $min_score;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function __toString(): string
    {
        return $this->title ?? ('Chapter #'.($this->id ?? 'n/a'));
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        // set the owning side of the relation if necessary
        if ($quiz !== null && $quiz->getChapter() !== $this) {
            $quiz->setChapter($this);
        }

        $this->quiz = $quiz;

        return $this;
    }
}
