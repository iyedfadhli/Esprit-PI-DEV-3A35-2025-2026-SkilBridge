<?php

namespace App\Entity;

use App\Repository\EducationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
class Education
{
    public function __construct()
    {
        $this->start_date = new \DateTime();
        $this->end_date = new \DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'educations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?cv $cv = null;

    #[ORM\Column(length: 30)]
    private string $degree = '';

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $field_of_study = null;

    #[ORM\Column(length: 30)]
    private string $school = '';

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $start_date;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $end_date;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
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

    public function getDegree(): string
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
        return $this->field_of_study;
    }

    public function setFieldOfStudy(?string $field_of_study): static
    {
        $this->field_of_study = $field_of_study;

        return $this;
    }

    public function getSchool(): string
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

    public function getStartDate(): \DateTime
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTime $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTime $end_date): static
    {
        $this->end_date = $end_date;

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
