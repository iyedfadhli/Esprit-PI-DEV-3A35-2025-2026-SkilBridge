<?php

namespace App\Entity;

use App\Repository\SponsorHackathonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SponsorHackathonRepository::class)]
class SponsorHackathon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?sponsor $sponsor = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?hackathon $hackathon = null;

    #[ORM\Column(length: 30)]
    private ?string $contribution_type = null;

    #[ORM\Column(nullable: true)]
    private ?float $contribution_value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSponsor(): ?sponsor
    {
        return $this->sponsor;
    }

    public function setSponsor(?sponsor $sponsor): static
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    public function getHackathon(): ?hackathon
    {
        return $this->hackathon;
    }

    public function setHackathon(?hackathon $hackathon): static
    {
        $this->hackathon = $hackathon;

        return $this;
    }

    public function getContributionType(): ?string
    {
        return $this->contribution_type;
    }

    public function setContributionType(string $contribution_type): static
    {
        $this->contribution_type = $contribution_type;

        return $this;
    }

    public function getContributionValue(): ?float
    {
        return $this->contribution_value;
    }

    public function setContributionValue(?float $contribution_value): static
    {
        $this->contribution_value = $contribution_value;

        return $this;
    }
}
