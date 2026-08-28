<?php
namespace App\Entity;
use App\Repository\FeaturedPublicationRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: FeaturedPublicationRepository::class)]
#[ORM\Table(name:'a_la_une')]
#[ORM\UniqueConstraint(name:'uniq_featured_position', columns:['position'])]
#[ORM\UniqueConstraint(name:'uniq_featured_publication', columns:['publication_id'])]
class FeaturedPublication
{
    #[ORM\Id,ORM\GeneratedValue,ORM\Column] private ?int $id=null;
    #[ORM\OneToOne(inversedBy:'featuredPublication'),ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private ?Publication $publication=null;
    #[ORM\Column] private int $position=1;
    public function getId():?int{return $this->id;} public function getPublication():?Publication{return $this->publication;} public function setPublication(Publication $v):static{$this->publication=$v;return $this;} public function getPosition():int{return $this->position;} public function setPosition(int $v):static{if($v<1||$v>10)throw new \InvalidArgumentException('Position 1..10');$this->position=$v;return $this;}
}
