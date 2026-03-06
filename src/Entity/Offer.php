<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $entreprise = null;

    #[ORM\Column(length: 30)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(length: 30)]
    private string $offer_type = '';

    #[ORM\Column(length: 30)]
    private string $field = '';

    #[ORM\Column(length: 30)]
    private string $required_level = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $required_skills = '';

    #[ORM\Column(length: 40)]
    private string $location = '';

    #[ORM\Column(length: 40)]
    private string $contract_type = '';

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $salary_range = null;

    #[ORM\Column(length: 30)]
    private string $status = '';

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreprise(): ?User
    {
        return $this->entreprise;
    }

    public function setEntreprise(?User $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
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

    public function getOfferType(): string
    {
        return $this->offer_type;
    }

    public function setOfferType(string $offer_type): static
    {
        $this->offer_type = $offer_type;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getRequiredLevel(): string
    {
        return $this->required_level;
    }

    public function setRequiredLevel(string $required_level): static
    {
        $this->required_level = $required_level;

        return $this;
    }

    public function getRequiredSkills(): string
    {
        return $this->required_skills;
    }

    public function setRequiredSkills(string $required_skills): static
    {
        $this->required_skills = $required_skills;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getContractType(): string
    {
        return $this->contract_type;
    }

    public function setContractType(string $contract_type): static
    {
        $this->contract_type = $contract_type;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSalaryRange(): ?string
    {
        return $this->salary_range;
    }

    public function setSalaryRange(float|int|string|null $salary_range): static
    {
        $this->salary_range = $salary_range === null ? null : number_format((float) $salary_range, 2, '.', '');

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
