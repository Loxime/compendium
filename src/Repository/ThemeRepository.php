<?php
namespace App\Repository;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ThemeRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry $r){parent::__construct($r,Theme::class);} }
