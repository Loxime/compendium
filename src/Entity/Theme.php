<?php
namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ORM\Table(name: 'themes')]
class Theme
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 150)] private string $nom = '';
    #[ORM\Column(length: 180, unique: true)] private string $slug = '';
    #[ORM\Column(length: 100)] private string $iconeFontawesome = 'fa-solid fa-book';
    #[ORM\Column] private int $ordre = 0;
    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Publication::class)] private Collection $publications;
    public function __construct(){ $this->publications=new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $v): static { $this->nom=$v; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $v): static { $this->slug=$v; return $this; }
    public function getIconeFontawesome(): string { return $this->iconeFontawesome; }
    public function setIconeFontawesome(string $v): static { $this->iconeFontawesome=$v; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $v): static { $this->ordre=$v; return $this; }
}
