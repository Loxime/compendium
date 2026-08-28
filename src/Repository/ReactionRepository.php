<?php
namespace App\Repository;
use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ReactionRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry $r){parent::__construct($r,Reaction::class);} }
