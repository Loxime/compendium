<?php
namespace App\Entity;
use App\Enum\ReactionType;
use App\Repository\ReactionRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: ReactionRepository::class)]
#[ORM\Table(name:'reactions')]
#[ORM\UniqueConstraint(name:'uniq_publication_user_reaction', columns:['publication_id','user_id'])]
class Reaction
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id=null;
    #[ORM\ManyToOne(inversedBy:'reactions'), ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private ?Publication $publication=null;
    #[ORM\ManyToOne(inversedBy:'reactions'), ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private ?User $user=null;
    #[ORM\Column(length:20, enumType:ReactionType::class)] private ReactionType $type=ReactionType::LIKE;
    #[ORM\Column] private \DateTimeImmutable $createdAt;
    public function __construct(){ $this->createdAt=new \DateTimeImmutable(); }
    public function getId():?int{return $this->id;} public function getPublication():?Publication{return $this->publication;} public function setPublication(Publication $v):static{$this->publication=$v;return $this;} public function getUser():?User{return $this->user;} public function setUser(User $v):static{$this->user=$v;return $this;} public function getType():ReactionType{return $this->type;} public function setType(ReactionType $v):static{$this->type=$v;return $this;}
}
