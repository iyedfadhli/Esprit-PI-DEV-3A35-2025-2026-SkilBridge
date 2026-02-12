<?php

namespace App\Entity;

use App\Repository\CertifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertifRepository::class)]
class Certif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?cv $cv = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 30)]
    private ?string $issued_by = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $issue_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $exp_date = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIssuedBy(): ?string
    {
        return $this->issued_by;
    }

    public function setIssuedBy(string $issued_by): static
    {
        $this->issued_by = $issued_by;

        return $this;
    }

    public function getIssueDate(): ?\DateTime
    {
        return $this->issue_date;
    }

    public function setIssueDate(\DateTime $issue_date): static
    {
        $this->issue_date = $issue_date;

        return $this;
    }

    public function getExpDate(): ?\DateTime
    {
        return $this->exp_date;
    }

    public function setExpDate(\DateTime $exp_date): static
    {
        $this->exp_date = $exp_date;

        return $this;
    }
}
