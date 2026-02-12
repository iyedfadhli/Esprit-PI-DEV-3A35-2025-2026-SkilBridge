<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Admin;
use App\Entity\Supervisor;
use App\Entity\Student;
use App\Entity\Entreprise;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'admin' => Admin::class,
    'supervisor' => Supervisor::class,
    'student' => Student::class,
    'entreprise' => Entreprise::class,
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateNaissance = null;

  #[ORM\Column(type:"string", length:180, unique:true)]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'Please enter a valid email address')]
    private $email;
//raz
    #[ORM\Column(options: ['default' => false])]
    private bool $ban = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(name: "passwd", length: 255)]
    private ?string $passwd = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateInscrit;

    #[ORM\Column(options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $report_nbr = 0;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $previous_role = null;

    public function __construct()
    {
        $this->ban = false;
        $this->report_nbr = 0;
        $this->is_active = true;
        $this->dateInscrit = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }
    public function setDateNaissance(?\DateTime $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function isBan(): bool
    {
        return $this->ban;
    }
    public function setBan(bool $ban): static
    {
        $this->ban = $ban;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }
    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->passwd;
    }

    public function setPassword(string $passwd): static
    {
        $this->passwd = $passwd;
        return $this;
    }

    public function getDateInscrit(): \DateTime
    {
        return $this->dateInscrit;
    }
    public function setDateInscrit(\DateTime $dateInscrit): static
    {
        $this->dateInscrit = $dateInscrit;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getReportNbr(): int
    {
        return $this->report_nbr;
    }
    public function setReportNbr(int $report_nbr): static
    {
        $this->report_nbr = $report_nbr;
        return $this;
    }
    public function getDisplayName(): string
    {
        if ($this->prenom && $this->nom) {
            return $this->prenom . ' ' . $this->nom;
        }
        if ($this->email) {
            return $this->email;
        }
        return 'Unknown';
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this instanceof Admin)
            $roles[] = 'ROLE_ADMIN';
        if ($this instanceof Supervisor)
            $roles[] = 'ROLE_SUPERVISOR';
        if ($this instanceof Student)
            $roles[] = 'ROLE_STUDENT';
        if ($this instanceof Entreprise)
            $roles[] = 'ROLE_ENTREPRISE';
        return array_unique($roles);
    }
     public function getMainRoleLabel(): string
    {
        $roles = $this->getRoles();

        if (in_array('ROLE_ADMIN', $roles))
            return 'Admin';
        if (in_array('ROLE_SUPERVISOR', $roles))
            return 'Supervisor';
        if (in_array('ROLE_STUDENT', $roles))
            return 'Student';
        if (in_array('ROLE_ENTREPRISE', $roles))
            return 'Entreprise';

        return 'User'; // fallback
    }
    public function setRoles($roles): self
{
    $this->type= $roles;
    return $this;
}



    public function eraseCredentials(): void
    {
    }

    public function getPreviousRole(): ?string
    {
        return $this->previous_role;
    }

    public function setPreviousRole(?string $previous_role): static
    {
        $this->previous_role = $previous_role;

        return $this;
    }
}

