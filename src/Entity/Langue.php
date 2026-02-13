<?php

namespace App\Entity;

use App\Repository\LangueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LangueRepository::class)]
class Langue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?cv $cv = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'La langue ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'La langue doit contenir au moins 2 caractères',
        maxMessage: 'La langue ne peut pas dépasser 30 caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le niveau ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Natif'],
        message: 'Le niveau sélectionné est invalide'
    )]
    private ?string $niveau = null;

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

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }
}