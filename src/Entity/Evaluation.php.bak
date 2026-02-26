<?php

namespace App\Entity;

use App\Repository\EducationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
class Education
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cv $cv = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le diplôme ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le diplôme doit contenir au moins 2 caractères',
        maxMessage: 'Le diplôme ne peut pas dépasser 100 caractères'
    )]
    private ?string $degree = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le domaine d\'étude ne peut pas dépasser 100 caractères'
    )]
    private ?string $fieldOfStudy = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'L\'établissement ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'L\'établissement doit contenir au moins 2 caractères',
        maxMessage: 'L\'établissement ne peut pas dépasser 100 caractères'
    )]
    private ?string $school = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'La ville ne peut pas dépasser 100 caractères'
    )]
    private ?string $city = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'La description ne peut pas dépasser 1000 caractères'
    )]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCv(): ?Cv
    {
        return $this->cv;
    }

    public function setCv(?Cv $cv): static
    {
        $this->cv = $cv;
        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(string $degree): static
    {
        $this->degree = $degree;
        return $this;
    }

    public function getFieldOfStudy(): ?string
    {
        return $this->fieldOfStudy;
    }

    public function setFieldOfStudy(?string $fieldOfStudy): static
    {
        $this->fieldOfStudy = $fieldOfStudy;
        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(string $school): static
    {
        $this->school = $school;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }
}