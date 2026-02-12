<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: "The group name cannot be empty")]
    #[Assert\Length(min: 3, max: 40, minMessage: "Name must be at least 3 characters")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Please provide a description")]
    #[Assert\Length(min: 10, minMessage: "Description must be at least 10 characters")]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    private ?string $level = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $leaderId = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Max members is required")]
    #[Assert\Positive(message: "Must be a positive number")]
    #[Assert\LessThan(101, message: "Maximum 100 members allowed")]
    private ?int $max_members = null;

    #[ORM\Column]
    private ?float $ratingScore = null;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLeaderId(): ?User
    {
        return $this->leaderId;
    }

    public function setLeaderId(?User $leaderId): static
    {
        $this->leaderId = $leaderId;

        return $this;
    }

    public function getMaxMembers(): ?int
    {
        return $this->max_members;
    }

    public function setMaxMembers(int $max_members): static
    {
        $this->max_members = $max_members;

        return $this;
    }

    public function getRatingScore(): ?float
    {
        return $this->ratingScore;
    }

    public function setRatingScore(float $ratingScore): static
    {
        $this->ratingScore = $ratingScore;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
