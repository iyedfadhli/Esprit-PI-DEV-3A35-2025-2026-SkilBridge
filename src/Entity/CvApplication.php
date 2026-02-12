<?php

namespace App\Entity;

use App\Repository\CvApplicationRepository;
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
    private ?cv $cv = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?offer $offer = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $applied_at = null;

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

    public function getOffer(): ?offer
    {
        return $this->offer;
    }

    public function setOffer(?offer $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAppliedAt(): ?\DateTimeImmutable
    {
        return $this->applied_at;
    }

    public function setAppliedAt(\DateTimeImmutable $applied_at): static
    {
        $this->applied_at = $applied_at;

        return $this;
    }
}
