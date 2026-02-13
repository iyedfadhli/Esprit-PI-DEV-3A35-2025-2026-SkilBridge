<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?cv $cv = null;

    #[ORM\Column(length: 35)]
    #[Assert\NotBlank(message: 'Le nom de la compétence ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 35,
        minMessage: 'Le nom doit contenir au moins 2 caractères',
        maxMessage: 'Le nom ne peut pas dépasser 35 caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le type de compétence est obligatoire')]
    #[Assert\Choice(
        choices: ['hard', 'soft'],
        message: 'Le type doit être "hard" ou "soft"'
    )]
    private ?string $type = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le niveau de compétence est obligatoire')]
    #[Assert\Choice(
        choices: ['Debutant', 'Intermediaire', 'Avance', 'Expert'],
        message: 'Le niveau sélectionné est invalide'
    )]
    private ?string $level = null;

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;
        return $this;
    }
}