<?php
namespace App\Repository;
use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class AvisRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry $r){parent::__construct($r,Avis::class);} }
