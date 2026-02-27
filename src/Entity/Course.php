<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Quiz;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Chapter;
use App\Entity\Enrollement;
use App\Entity\Challenge;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    /**
     * Constantes de difficulté pour le système de recommandation
     */
    public const DIFFICULTY_BEGINNER = 'BEGINNER';
    public const DIFFICULTY_INTERMEDIATE = 'INTERMEDIATE';
    public const DIFFICULTY_ADVANCED = 'ADVANCED';

    /**
     * Mapping difficulté → niveau numérique pour le calcul de progression
     */
    public const DIFFICULTY_LEVELS = [
        self::DIFFICULTY_BEGINNER => 1,
        self::DIFFICULTY_INTERMEDIATE => 2,
        self::DIFFICULTY_ADVANCED => 3,
    ];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course:recommendation'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['course:recommendation'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['course:recommendation'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['course:recommendation'])]
    private ?int $duration = null;

    /** Niveau de difficulté du cours : BEGINNER, INTERMEDIATE, ADVANCED */
    #[ORM\Column(length: 20, options: ['default' => 'BEGINNER'])]
    #[Groups(['course:recommendation'])]
    private ?string $difficulty = self::DIFFICULTY_BEGINNER;

    /** Indique si le cours est actif et visible pour les étudiants */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['course:recommendation'])]
    private bool $isActive = true;

    #[ORM\Column]
    private ?float $validation_score = null;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $creator = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $material = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Quiz $prerequisite_quiz = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Quiz::class, cascade: ['persist', 'remove'])]
    private Collection $quizzes;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Chapter::class, cascade: ['persist', 'remove'])]
    private Collection $chapters;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollement::class, cascade: ['remove'])]
    private Collection $enrollements;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Challenge::class, cascade: ['remove'])]
    private Collection $challenges;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $sections_to_review = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getValidationScore(): ?float
    {
        return $this->validation_score;
    }

    public function setValidationScore(float $validation_score): static
    {
        $this->validation_score = $validation_score;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(?string $material): static
    {
        $this->material = $material;

        return $this;
    }

    public function getPrerequisiteQuiz(): ?Quiz
    {
        return $this->prerequisite_quiz;
    }

    public function setPrerequisiteQuiz(?Quiz $prerequisite_quiz): static
    {
        $this->prerequisite_quiz = $prerequisite_quiz;

        return $this;
    }

    public function getSectionsToReview(): ?array
    {
        return $this->sections_to_review;
    }

    public function setSectionsToReview(?array $sections_to_review): static
    {
        $this->sections_to_review = $sections_to_review;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Retourne le niveau numérique de difficulté (1=BEGINNER, 2=INTERMEDIATE, 3=ADVANCED)
     */
    public function getDifficultyLevel(): int
    {
        return self::DIFFICULTY_LEVELS[$this->difficulty] ?? 1;
    }

    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
        $this->chapters = new ArrayCollection();
        $this->enrollements = new ArrayCollection();
        $this->challenges = new ArrayCollection();
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setCourse($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            if ($quiz->getCourse() === $this) {
                $quiz->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setCourse($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            if ($chapter->getCourse() === $this) {
                $chapter->setCourse(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'Course #'.$this->id;
    }
}