<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    public function __construct()
    {
        $this->registred_at = new \DateTimeImmutable();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Hackathon $hackathon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Group $group_id = null;

    #[ORM\Column(length: 30)]
    private string $status = '';

    #[ORM\Column(length: 30)]
    private string $payment_status = '';

    #[ORM\Column(length: 30)]
    private string $payment_ref = '';

    #[ORM\Column]
    private \DateTimeImmutable $registred_at;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupId(): ?Group
    {
        return $this->group_id;
    }

    public function setGroupId(?Group $group_id): static
    {
        $this->group_id = $group_id;

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

    public function getPaymentStatus(): string
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(string $payment_status): static
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    public function getPaymentRef(): string
    {
        return $this->payment_ref;
    }

    public function setPaymentRef(string $payment_ref): static
    {
        $this->payment_ref = $payment_ref;

        return $this;
    }

    public function getRegistredAt(): \DateTimeImmutable
    {
        return $this->registred_at;
    }

    public function setRegistredAt(\DateTimeImmutable $registred_at): static
    {
        $this->registred_at = $registred_at;

        return $this;
    }
}
