<?php
namespace App\Entity;

use App\Enum\ContentFormat;
use App\Enum\PublicationSource;
use App\Enum\PublicationStatus;
use App\Enum\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ORM\Table(name: 'publications')]
#[ORM\HasLifecycleCallbacks]
class Publication
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 20, enumType: PublicationType::class)] private PublicationType $type = PublicationType::NOTE;
    #[ORM\Column(length: 255)] private string $titre = '';
    #[ORM\Column(type: Types::TEXT)] private string $contenu = '';
    #[ORM\Column(length: 30, enumType: ContentFormat::class)] private ContentFormat $contenuFormat = ContentFormat::TEXTE_BRUT;
    #[ORM\ManyToOne(inversedBy: 'publications'), ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')] private ?Theme $theme = null;
    #[ORM\Column(length: 10)] private string $langue = 'fr';
    #[ORM\Column(length: 36, nullable: true)] private ?string $groupeTraductionId = null;
    #[ORM\Column(length: 20, enumType: PublicationStatus::class)] private PublicationStatus $statut = PublicationStatus::BROUILLON;
    #[ORM\Column(length: 20, enumType: PublicationSource::class)] private PublicationSource $source = PublicationSource::MANUEL;
    #[ORM\Column(length: 255, nullable: true)] private ?string $driveFileId = null;
    #[ORM\Column] private int $nbLikes = 0;
    #[ORM\Column] private int $nbDislikes = 0;
    #[ORM\Column] private \DateTimeImmutable $createdAt;
    #[ORM\Column] private \DateTimeImmutable $updatedAt;
    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: Avis::class, cascade: ['remove'])] private Collection $avis;
    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: Reaction::class, cascade: ['remove'])] private Collection $reactions;
    #[ORM\OneToOne(mappedBy: 'publication', targetEntity: FeaturedPublication::class, cascade: ['remove'])] private ?FeaturedPublication $featuredPublication = null;

    public function __construct(){ $now=new \DateTimeImmutable(); $this->createdAt=$now; $this->updatedAt=$now; $this->avis=new ArrayCollection(); $this->reactions=new ArrayCollection(); }
    #[ORM\PreUpdate] public function touch(): void { $this->updatedAt=new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getType(): PublicationType { return $this->type; }
    public function setType(PublicationType $v): static { $this->type=$v; return $this; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre=$v; return $this; }
    public function getContenu(): string { return $this->contenu; }
    public function setContenu(string $v): static { $this->contenu=$v; return $this; }
    public function getContenuFormat(): ContentFormat { return $this->contenuFormat; }
    public function setContenuFormat(ContentFormat $v): static { $this->contenuFormat=$v; return $this; }
    public function getTheme(): ?Theme { return $this->theme; }
    public function setTheme(Theme $v): static { $this->theme=$v; return $this; }
    public function getLangue(): string { return $this->langue; }
    public function setLangue(string $v): static { $this->langue=strtolower($v); return $this; }
    public function getGroupeTraductionId(): ?string { return $this->groupeTraductionId; }
    public function setGroupeTraductionId(?string $v): static { $this->groupeTraductionId=$v; return $this; }
    public function getStatut(): PublicationStatus { return $this->statut; }
    public function setStatut(PublicationStatus $v): static { $this->statut=$v; return $this; }
    public function getSource(): PublicationSource { return $this->source; }
    public function setSource(PublicationSource $v): static { $this->source=$v; return $this; }
    public function getDriveFileId(): ?string { return $this->driveFileId; }
    public function setDriveFileId(?string $v): static { $this->driveFileId=$v; return $this; }
    public function getNbLikes(): int { return $this->nbLikes; }
    public function setNbLikes(int $v): static { $this->nbLikes=max(0,$v); return $this; }
    public function getNbDislikes(): int { return $this->nbDislikes; }
    public function setNbDislikes(int $v): static { $this->nbDislikes=max(0,$v); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getAvis(): Collection { return $this->avis; }
}
