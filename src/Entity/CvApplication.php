<?php

namespace App\Entity;

use App\Repository\CvApplicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CvApplicationRepository::class)]
class CvApplication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cv $cv = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Offer $offer = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $applied_at = null;

    // --- ATS Scoring ---------------------------------------------------------

    #[ORM\Column(nullable: true)]
    private ?int $atsScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $skillsScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $experienceScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $educationScore = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $matchedSkills = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $missingSkills = null;

    #[ORM\Column(nullable: true)]
    private ?int $aiScore = null;

    // --- Getters / Setters ----------------------------------------------------

    public function getId(): ?int { return $this->id; }

    public function getCv(): ?Cv { return $this->cv; }
    public function setCv(?Cv $cv): static { $this->cv = $cv; return $this; }

    public function getOffer(): ?Offer { return $this->offer; }
    public function setOffer(?Offer $offer): static { $this->offer = $offer; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getAppliedAt(): ?\DateTimeImmutable { return $this->applied_at; }
    public function setAppliedAt(\DateTimeImmutable $applied_at): static { $this->applied_at = $applied_at; return $this; }

    public function getAtsScore(): ?int { return $this->atsScore; }
    public function setAtsScore(?int $atsScore): static { $this->atsScore = $atsScore; return $this; }

    public function getSkillsScore(): ?int { return $this->skillsScore; }
    public function setSkillsScore(?int $skillsScore): static { $this->skillsScore = $skillsScore; return $this; }

    public function getExperienceScore(): ?int { return $this->experienceScore; }
    public function setExperienceScore(?int $experienceScore): static { $this->experienceScore = $experienceScore; return $this; }

    public function getEducationScore(): ?int { return $this->educationScore; }
    public function setEducationScore(?int $educationScore): static { $this->educationScore = $educationScore; return $this; }

    public function getMatchedSkills(): ?array { return $this->matchedSkills; }
    public function setMatchedSkills(?array $matchedSkills): static { $this->matchedSkills = $matchedSkills; return $this; }

    public function getMissingSkills(): ?array { return $this->missingSkills; }
    public function setMissingSkills(?array $missingSkills): static { $this->missingSkills = $missingSkills; return $this; }

    public function getAiScore(): ?int { return $this->aiScore; }
    public function setAiScore(?int $aiScore): static { $this->aiScore = $aiScore; return $this; }
}
