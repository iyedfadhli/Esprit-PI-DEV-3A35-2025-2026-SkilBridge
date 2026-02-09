<?php

namespace App\Entity;

use App\Repository\HackathonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HackathonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Hackathon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator_id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(max: 30, maxMessage: 'Title cannot exceed 30 characters')]
    private ?string $title = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Theme is required')]
    #[Assert\Length(max: 30, maxMessage: 'Theme cannot exceed 30 characters')]
    private ?string $theme = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Description is required')]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Rules are required')]
    private ?string $rules = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Start date is required')]
    #[Assert\Type('\DateTimeImmutable')]
    private ?\DateTimeImmutable $start_at = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'End date is required')]
    #[Assert\Type('\DateTimeImmutable')]
    #[Assert\GreaterThan(propertyPath: 'start_at', message: 'End date must be after start date')]
    private ?\DateTimeImmutable $end_at = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Registration open date is required')]
    private ?\DateTime $registration_open_at = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Registration close date is required')]
    #[Assert\Type('\DateTimeImmutable')]
    #[Assert\LessThan(propertyPath: 'start_at', message: 'Registration must close before the event starts')]
    private ?\DateTimeImmutable $registration_close_at = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Fee is required')]
    #[Assert\PositiveOrZero(message: 'Fee cannot be negative')]
    private ?float $fee = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Max teams is required')]
    #[Assert\Positive(message: 'Max teams must be at least 1')]
    private ?int $max_teams = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Max team size is required')]
    #[Assert\Positive(message: 'Max team size must be at least 1')]
    private ?int $team_size_max = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Location is required')]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Cover URL is required')]
    #[Assert\Url(message: 'The URL is not a valid URL')]
    private ?string $cover_url = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Status is required')]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'hackathon', targetEntity: SponsorHackathon::class, orphanRemoval: true)]
    private Collection $sponsorHackathons;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->sponsorHackathons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatorId(): ?User
    {
        return $this->creator_id;
    }

    public function setCreatorId(?User $creator_id): static
    {
        $this->creator_id = $creator_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;

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

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function setRules(string $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->start_at;
    }

    public function setStartAt(\DateTimeImmutable $start_at): static
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->end_at;
    }

    public function setEndAt(\DateTimeImmutable $end_at): static
    {
        $this->end_at = $end_at;

        return $this;
    }

    public function getRegistrationOpenAt(): ?\DateTime
    {
        return $this->registration_open_at;
    }

    public function setRegistrationOpenAt(\DateTime $registration_open_at): static
    {
        $this->registration_open_at = $registration_open_at;

        return $this;
    }

    public function getRegistrationCloseAt(): ?\DateTimeImmutable
    {
        return $this->registration_close_at;
    }

    public function setRegistrationCloseAt(\DateTimeImmutable $registration_close_at): static
    {
        $this->registration_close_at = $registration_close_at;

        return $this;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(float $fee): static
    {
        $this->fee = $fee;

        return $this;
    }

    public function getMaxTeams(): ?int
    {
        return $this->max_teams;
    }

    public function setMaxTeams(int $max_teams): static
    {
        $this->max_teams = $max_teams;

        return $this;
    }

    public function getTeamSizeMax(): ?int
    {
        return $this->team_size_max;
    }

    public function setTeamSizeMax(int $team_size_max): static
    {
        $this->team_size_max = $team_size_max;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->cover_url;
    }

    public function setCoverUrl(string $cover_url): static
    {
        $this->cover_url = $cover_url;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, SponsorHackathon>
     */
    public function getSponsorHackathons(): Collection
    {
        return $this->sponsorHackathons;
    }

    public function addSponsorHackathon(SponsorHackathon $sponsorHackathon): static
    {
        if (!$this->sponsorHackathons->contains($sponsorHackathon)) {
            $this->sponsorHackathons->add($sponsorHackathon);
            $sponsorHackathon->setHackathon($this);
        }

        return $this;
    }

    public function removeSponsorHackathon(SponsorHackathon $sponsorHackathon): static
    {
        if ($this->sponsorHackathons->removeElement($sponsorHackathon)) {
            // set the owning side to null (unless already changed)
            if ($sponsorHackathon->getHackathon() === $this) {
                $sponsorHackathon->setHackathon(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }
}
