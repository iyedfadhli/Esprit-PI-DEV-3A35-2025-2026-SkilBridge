<?php

namespace App\Entity;

use App\Repository\SponsorHackathonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SponsorHackathonRepository::class)]
class SponsorHackathon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Sponsor::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Sponsor is required')]
    private ?Sponsor $sponsor = null;

    #[ORM\ManyToOne(targetEntity: Hackathon::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Hackathon is required')]
    private ?Hackathon $hackathon = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Please enter the contribution type')]
    #[Assert\Length(
        max: 30,
        maxMessage: 'Contribution type cannot exceed 30 characters'
    )]
    private ?string $contribution_type = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'Please enter the contribution value')]
    #[Assert\PositiveOrZero(message: 'The value must be zero or greater')]
    private ?float $contribution_value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSponsor(): ?Sponsor
    {
        return $this->sponsor;
    }

    public function setSponsor(?Sponsor $sponsor): static
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    public function getHackathon(): ?Hackathon
    {
        return $this->hackathon;
    }

    public function setHackathon(?Hackathon $hackathon): static
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
