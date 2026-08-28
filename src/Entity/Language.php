<?php
namespace App\Entity;
use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[ORM\Table(name:'langues')]
class Language
{
    #[ORM\Id,ORM\GeneratedValue,ORM\Column] private ?int $id=null;
    #[ORM\Column(length:10,unique:true)] private string $code='fr';
    #[ORM\Column(length:100)] private string $nom='Français';
    #[ORM\Column] private bool $actif=true;
    public function getId():?int{return $this->id;} public function getCode():string{return $this->code;} public function setCode(string $v):static{$this->code=strtolower($v);return $this;} public function getNom():string{return $this->nom;} public function setNom(string $v):static{$this->nom=$v;return $this;} public function isActif():bool{return $this->actif;} public function setActif(bool $v):static{$this->actif=$v;return $this;}
}
