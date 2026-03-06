<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->deadLine = $now;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(length: 30)]
    private string $targetSkill = '';

    #[ORM\Column(length: 30)]
    private string $difficulty = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $creator = null;

    #[ORM\Column]
    private int $minGroupNbr = 0;

    #[ORM\Column]
    private int $maxGroupNbr = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $deadLine;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'challenges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Course $course = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTargetSkill(): string
    {
        return $this->targetSkill;
    }

    public function setTargetSkill(string $targetSkill): static
    {
        $this->targetSkill = $targetSkill;

        return $this;
    }

    public function getDifficulty(): string
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

    protected function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function assignCreator(User $creator): static
    {
        return $this->setCreator($creator);
    }

    public function getMinGroupNbr(): int
    {
        return $this->minGroupNbr;
    }

    public function setMinGroupNbr(int $minGroupNbr): static
    {
        $this->minGroupNbr = $minGroupNbr;

        return $this;
    }

    public function getMaxGroupNbr(): int
    {
        return $this->maxGroupNbr;
    }

    public function setMaxGroupNbr(int $maxGroupNbr): static
    {
        $this->maxGroupNbr = $maxGroupNbr;

        return $this;
    }

    public function getDeadLine(): \DateTimeImmutable
    {
        return $this->deadLine;
    }

    public function setDeadLine(\DateTimeInterface $deadLine): static
    {
        $this->deadLine = $deadLine instanceof \DateTimeImmutable ? $deadLine : \DateTimeImmutable::createFromMutable($deadLine);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt instanceof \DateTimeImmutable ? $createdAt : \DateTimeImmutable::createFromMutable($createdAt);

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
