<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
   

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $education = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    private ?string $skills = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreGenerale = 0;






   

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setEducation(string $education): static
    {
        $this->education = $education;

        return $this;
    }

    public function getSkills(): ?string
    {
        return $this->skills;
    }

    public function setSkills(string $skills): static
    {
        $this->skills = $skills;

        return $this;
    }

    public function getScoreGenerale(): ?int
    {
        return $this->scoreGenerale;
    }

    public function setScoreGenerale(int $scoreGenerale): static
    {
        $this->scoreGenerale = $scoreGenerale;

        return $this;
    }

   

   
}
