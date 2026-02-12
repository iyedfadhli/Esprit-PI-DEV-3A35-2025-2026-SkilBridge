<?php

namespace App\Entity;

use App\Repository\MemberActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberActivityRepository::class)]
class MemberActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Activity $id_activity = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user_id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $activityDescription = null;

    #[ORM\Column]
    private ?float $indivScore = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdActivity(): ?Activity
    {
        return $this->id_activity;
    }

    public function setIdActivity(?Activity $id_activity): static
    {
        $this->id_activity = $id_activity;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getActivityDescription(): ?string
    {
        return $this->activityDescription;
    }

    public function setActivityDescription(string $activityDescription): static
    {
        $this->activityDescription = $activityDescription;

        return $this;
    }

    public function getIndivScore(): ?float
    {
        return $this->indivScore;
    }

    public function setIndivScore(float $indivScore): static
    {
        $this->indivScore = $indivScore;

        return $this;
    }
}
