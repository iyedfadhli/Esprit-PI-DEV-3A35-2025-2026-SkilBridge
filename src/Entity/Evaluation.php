<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false,onDelete:"CASCADE")]
    private ?Activity $activity_id = null;

    #[ORM\Column(nullable: true)]
    private ?float $groupScore = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedback = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pre_feedback = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityId(): ?Activity
    {
        return $this->activity_id;
    }

    public function setActivityId(Activity $activity_id): static
    {
        $this->activity_id = $activity_id;

        return $this;
    }

    public function getGroupScore(): ?float
    {
        return $this->groupScore;
    }

    public function setGroupScore(float $groupScore): static
    {
        $this->groupScore = $groupScore;

        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(string $feedback): static
    {
        $this->feedback = $feedback;

        return $this;
    }

    public function getPreFeedback(): ?string
    {
        return $this->pre_feedback;
    }

    public function setPreFeedback(?string $pre_feedback): static
    {
        $this->pre_feedback = $pre_feedback;

        return $this;
    }
}
