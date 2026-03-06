<?php

namespace App\Entity;

use App\Repository\PostsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostsRepository::class)]
class Posts
{
    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Post content cannot be empty")]
    #[Assert\Length(min: 5, minMessage: "Content must be at least 5 characters")]
    private string $description = '';

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Title is required")]
    #[Assert\Length(max: 30, maxMessage: "Title cannot exceed 30 characters")]
    private string $titre = '';

    #[ORM\Column(length: 30)]
    private string $status = '';

    #[ORM\Column(length: 30)]
    private string $visibility = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attached_file = null;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private int $likes_counter = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Group $group_id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $Author_id = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getAttachedFile(): ?string
    {
        return $this->attached_file;
    }

    public function setAttachedFile(?string $attached_file): static
    {
        $this->attached_file = $attached_file;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLikesCounter(): int
    {
        return $this->likes_counter;
    }

    public function setLikesCounter(int $likes_counter): static
    {
        $this->likes_counter = $likes_counter;

        return $this;
    }

    public function getGroupId(): ?Group
    {
        return $this->group_id;
    }

    public function setGroupId(?Group $group_id): static
    {
        $this->group_id = $group_id;

        return $this;
    }

    public function getAuthorId(): ?User
    {
        return $this->Author_id;
    }

    public function setAuthorId(?User $Author_id): static
    {
        $this->Author_id = $Author_id;

        return $this;
    }
}
