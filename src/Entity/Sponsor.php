<?php

namespace App\Entity;

use App\Repository\SponsorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Creator is required')]
    private ?User $creator_id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Sponsor name is required')]
    #[Assert\Length(
        max: 30,
        maxMessage: 'Name cannot exceed 30 characters'
    )]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Description is required')]
    private string $description = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Logo URL is required')]
    #[Assert\Url(message: 'Please enter a valid URL')]
    private string $logo_url = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        message: 'Please enter a valid URL',
        protocols: ['http', 'https']
    )]
    private ?string $website_url = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Created at is required')]
    private \DateTimeImmutable $created_at;

    public function __construct()
    {
        // Set creation date by default so validation passes and DB constraint is satisfied
        $this->created_at = new \DateTimeImmutable();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getLogoUrl(): string
    {
        return $this->logo_url;
    }

    public function setLogoUrl(string $logo_url): static
    {
        $this->logo_url = $logo_url;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->website_url;
    }

    public function setWebsiteUrl(?string $website_url): static
    {
        $this->website_url = $website_url;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at instanceof \DateTimeImmutable ? $created_at : \DateTimeImmutable::createFromMutable($created_at);

        return $this;
    }
}
