<?php

namespace App\Entity;

use App\Repository\LessonsLearnedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonsLearnedRepository::class)]
class LessonsLearned
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Activity $id_activity = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $lessonDescription = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdActivity(): ?Activity
    {
        return $this->id_activity;
    }

    public function setIdActivity(?Activity $id_activity): static
    {
        $this->id_activity = $id_activity;

        return $this;
    }

    public function getLessonDescription(): string
    {
        return $this->lessonDescription;
    }

    public function setLessonDescription(string $lessonDescription): static
    {
        $this->lessonDescription = $lessonDescription;

        return $this;
    }
}
