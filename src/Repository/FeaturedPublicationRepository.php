<?php
namespace App\Repository;
use App\Entity\FeaturedPublication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class FeaturedPublicationRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry $r){parent::__construct($r,FeaturedPublication::class);} }
