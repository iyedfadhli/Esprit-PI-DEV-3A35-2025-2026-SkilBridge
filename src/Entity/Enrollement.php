<?php

namespace App\Entity;

use App\Repository\EnrollementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Course;

#[ORM\Entity(repositoryClass: EnrollementRepository::class)]
class Enrollement
{
    public function __construct()
    {
        $this->completed_at = new \DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $student = null;

    #[ORM\Column(length: 30)]
    private string $status = '';

    #[ORM\Column]
    private int $progress = 0;

    #[ORM\Column(nullable: true)]
    private ?float $score = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $completed_at;

    #[ORM\ManyToOne(inversedBy: 'enrollements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Course $course = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): static
    {
        $this->progress = $progress;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getCompletedAt(): \DateTime
    {
        return $this->completed_at;
    }

    public function setCompletedAt(\DateTime $completed_at): static
    {
        $this->completed_at = $completed_at;

        return $this;
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
}
