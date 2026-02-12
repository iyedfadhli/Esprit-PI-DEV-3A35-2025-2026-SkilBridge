<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Challenge $idChallenge = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Group $group_id = null;

    #[ORM\Column(length: 255)]
    private ?string $submission_file = null;

    #[ORM\Column]
    private ?\DateTime $submission_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdChallenge(): ?Challenge
    {
        return $this->idChallenge;
    }

    public function setIdChallenge(?Challenge $idChallenge): static
    {
        $this->idChallenge = $idChallenge;

        return $this;
    }

    public function getGroupId(): ?Group
    {
        return $this->group_id;
    }

    public function setGroupId(Group $group_id): static
    {
        $this->group_id = $group_id;

        return $this;
    }

    public function getSubmissionFile(): ?string
    {
        return $this->submission_file;
    }

    public function setSubmissionFile(string $submission_file): static
    {
        $this->submission_file = $submission_file;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTime
    {
        return $this->submission_date;
    }

    public function setSubmissionDate(\DateTime $submission_date): static
    {
        $this->submission_date = $submission_date;

        return $this;
    }
}
