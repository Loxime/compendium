<?php
namespace App\Repository;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class LanguageRepository extends ServiceEntityRepository { public function __construct(ManagerRegistry $r){parent::__construct($r,Language::class);} }
