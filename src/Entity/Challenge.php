<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    private ?string $targetSkill = null;

    #[ORM\Column(length: 30)]
    private ?string $difficulty = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\Column]
    private ?int $minGroupNbr = null;

    #[ORM\Column]
    private ?int $maxGroupNbr = null;

    #[ORM\Column]
    private ?\DateTime $deadLine = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTargetSkill(): ?string
    {
        return $this->targetSkill;
    }

    public function setTargetSkill(string $targetSkill): static
    {
        $this->targetSkill = $targetSkill;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getMinGroupNbr(): ?int
    {
        return $this->minGroupNbr;
    }

    public function setMinGroupNbr(int $minGroupNbr): static
    {
        $this->minGroupNbr = $minGroupNbr;

        return $this;
    }

    public function getMaxGroupNbr(): ?int
    {
        return $this->maxGroupNbr;
    }

    public function setMaxGroupNbr(int $maxGroupNbr): static
    {
        $this->maxGroupNbr = $maxGroupNbr;

        return $this;
    }

    public function getDeadLine(): ?\DateTime
    {
        return $this->deadLine;
    }

    public function setDeadLine(\DateTime $deadLine): static
    {
        $this->deadLine = $deadLine;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
