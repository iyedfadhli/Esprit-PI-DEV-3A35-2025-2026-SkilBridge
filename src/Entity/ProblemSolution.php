<?php

namespace App\Entity;

use App\Repository\ProblemSolutionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProblemSolutionRepository::class)]
class ProblemSolution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Activity $activityId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $problemDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $groupSolution = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $supervisorSolution = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityId(): ?Activity
    {
        return $this->activityId;
    }

    public function setActivityId(?Activity $activityId): static
    {
        $this->activityId = $activityId;

        return $this;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(string $problemDescription): static
    {
        $this->problemDescription = $problemDescription;

        return $this;
    }

    public function getGroupSolution(): ?string
    {
        return $this->groupSolution;
    }

    public function setGroupSolution(?string $groupSolution): static
    {
        $this->groupSolution = $groupSolution;

        return $this;
    }

    public function getSupervisorSolution(): ?string
    {
        return $this->supervisorSolution;
    }

    public function setSupervisorSolution(string $supervisorSolution): static
    {
        $this->supervisorSolution = $supervisorSolution;

        return $this;
    }
}
