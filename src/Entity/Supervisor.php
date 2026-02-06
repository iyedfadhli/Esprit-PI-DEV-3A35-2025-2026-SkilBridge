<?php

namespace App\Entity;

use App\Repository\SupervisorRepository;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupervisorRepository::class)]
class Supervisor extends User
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $experience = null;
     public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(string $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

}
