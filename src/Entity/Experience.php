<?php

namespace App\Entity;

use App\Repository\ExperienceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?cv $cv = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le titre du poste ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Le titre doit contenir au moins 2 caractères',
        maxMessage: 'Le titre ne peut pas dépasser 30 caractères'
    )]
    private ?string $job_title = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'L\'entreprise doit contenir au moins 2 caractères',
        maxMessage: 'L\'entreprise ne peut pas dépasser 30 caractères'
    )]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le lieu ne peut pas dépasser 255 caractères'
    )]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $end_date = null;

    #[ORM\Column]
    #[Assert\Type(type: 'boolean', message: 'La valeur doit être un booléen')]
    private ?bool $currently_working = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: 'La description doit contenir au moins 10 caractères',
        maxMessage: 'La description ne peut pas dépasser 2000 caractères'
    )]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCv(): ?cv
    {
        return $this->cv;
    }

    public function setCv(?cv $cv): static
    {
        $this->cv = $cv;
        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->job_title;
    }

    public function setJobTitle(string $job_title): static
    {
        $this->job_title = $job_title;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTime $start_date): static
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTime $end_date): static
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function isCurrentlyWorking(): ?bool
    {
        return $this->currently_working;
    }

    public function setCurrentlyWorking(bool $currently_working): static
    {
        $this->currently_working = $currently_working;
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
}