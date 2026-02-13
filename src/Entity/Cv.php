<?php

namespace App\Entity;

use App\Repository\CvRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CvRepository::class)]
class Cv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le nom du CV est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Le nom du CV doit contenir au moins 2 caractères',
        maxMessage: 'Le nom du CV ne peut pas dépasser 30 caractères'
    )]
    private ?string $nomCv = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'La langue est obligatoire')]
    #[Assert\Choice(
        choices: ['Francais', 'Anglais', 'Arabe'],
        message: 'La langue sélectionnée est invalide'
    )]
    private ?string $langue = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'L\'ID template doit être un nombre positif')]
    private ?int $idTemplate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: 'La progression doit être entre 0 et 100'
    )]
    private ?int $progression = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de création est obligatoire')]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL LinkedIn doit être valide')]
    private ?string $linkedinUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: 'Le résumé est obligatoire')]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Le résumé ne peut pas dépasser 1000 caractères'
    )]
    private ?string $summary = null;

    #[ORM\OneToMany(mappedBy: 'cv', targetEntity: Experience::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $experiences;

    #[ORM\OneToMany(mappedBy: 'cv', targetEntity: Education::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $educations;

    #[ORM\OneToMany(mappedBy: 'cv', targetEntity: Skill::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $skills;

    #[ORM\OneToMany(mappedBy: 'cv', targetEntity: Certif::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $certifs;

    #[ORM\OneToMany(mappedBy: 'cv', targetEntity: Langue::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $languages;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->educations = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->certifs = new ArrayCollection();
        $this->languages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCv(): ?string
    {
        return $this->nomCv;
    }

    public function setNomCv(?string $nomCv): static
    {
        $this->nomCv = $nomCv;
        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(?string $langue): static
    {
        $this->langue = $langue;
        return $this;
    }

    public function getIdTemplate(): ?int
    {
        return $this->idTemplate;
    }

    public function setIdTemplate(?int $idTemplate): static
    {
        $this->idTemplate = $idTemplate;
        return $this;
    }

    public function getProgression(): ?int
    {
        return $this->progression;
    }

    public function setProgression(?int $progression): static
    {
        $this->progression = $progression;
        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): static
    {
        $this->linkedinUrl = $linkedinUrl;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @return Collection<int, Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): static
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences->add($experience);
            $experience->setCv($this);
        }
        return $this;
    }

    public function removeExperience(Experience $experience): static
    {
        if ($this->experiences->removeElement($experience)) {
            if ($experience->getCv() === $this) {
                $experience->setCv(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Education>
     */
    public function getEducations(): Collection
    {
        return $this->educations;
    }

    public function addEducation(Education $education): static
    {
        if (!$this->educations->contains($education)) {
            $this->educations->add($education);
            $education->setCv($this);
        }
        return $this;
    }

    public function removeEducation(Education $education): static
    {
        if ($this->educations->removeElement($education)) {
            if ($education->getCv() === $this) {
                $education->setCv(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setCv($this);
        }
        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            if ($skill->getCv() === $this) {
                $skill->setCv(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Certif>
     */
    public function getCertifs(): Collection
    {
        return $this->certifs;
    }

    public function addCertif(Certif $certif): static
    {
        if (!$this->certifs->contains($certif)) {
            $this->certifs->add($certif);
            $certif->setCv($this);
        }
        return $this;
    }

    public function removeCertif(Certif $certif): static
    {
        if ($this->certifs->removeElement($certif)) {
            if ($certif->getCv() === $this) {
                $certif->setCv(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Langue>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Langue $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setCv($this);
        }
        return $this;
    }

    public function removeLanguage(Langue $language): static
    {
        if ($this->languages->removeElement($language)) {
            if ($language->getCv() === $this) {
                $language->setCv(null);
            }
        }
        return $this;
    }
}