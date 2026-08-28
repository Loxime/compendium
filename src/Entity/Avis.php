<?php
namespace App\Entity;
use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
class Avis
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id=null;
    #[ORM\ManyToOne(inversedBy: 'avis'), ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private ?Publication $publication=null;
    #[ORM\ManyToOne(inversedBy: 'avis'), ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?User $user=null;
    #[ORM\Column(type:Types::TEXT)] private string $contenu='';
    #[ORM\Column] private \DateTimeImmutable $createdAt;
    public function __construct(){ $this->createdAt=new \DateTimeImmutable(); }
    public function getId(): ?int{return $this->id;} public function getPublication():?Publication{return $this->publication;} public function setPublication(Publication $v):static{$this->publication=$v;return $this;} public function getUser():?User{return $this->user;} public function setUser(?User $v):static{$this->user=$v;return $this;} public function getContenu():string{return $this->contenu;} public function setContenu(string $v):static{$this->contenu=$v;return $this;} public function getCreatedAt():\DateTimeImmutable{return $this->createdAt;}
}
