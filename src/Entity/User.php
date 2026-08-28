<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 100)] private string $prenom = '';
    #[ORM\Column(length: 100)] private string $nom = '';
    #[ORM\Column(length: 180, unique: true)] private string $email = '';
    #[ORM\Column(length: 20, nullable: true)] private ?string $codePostal = null;
    #[ORM\Column(type: Types::JSON)] private array $roles = ['ROLE_USER'];
    #[ORM\Column] private \DateTimeImmutable $createdAt;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Avis::class)] private Collection $avis;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reaction::class)] private Collection $reactions;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WebauthnCredential::class, cascade: ['remove'], orphanRemoval: true)] private Collection $passkeys;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); $this->avis = new ArrayCollection(); $this->reactions = new ArrayCollection(); $this->passkeys = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $v): static { $this->prenom=$v; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $v): static { $this->nom=$v; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): static { $this->email=mb_strtolower(trim($v)); return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $v): static { $this->codePostal=$v; return $this; }
    public function getRoles(): array { $roles=$this->roles; $roles[]='ROLE_USER'; return array_values(array_unique($roles)); }
    public function setRoles(array $roles): static { $this->roles=array_values(array_unique($roles)); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getPasskeys(): Collection { return $this->passkeys; }
    public function getUserIdentifier(): string { return $this->email; }
    public function eraseCredentials(): void {}
}
