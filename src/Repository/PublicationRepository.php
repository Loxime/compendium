<?php
namespace App\Repository;
use App\Entity\Publication;
use App\Enum\PublicationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r){parent::__construct($r,Publication::class);}
    public function latestPublished(int $limit=20): array { return $this->createQueryBuilder('p')->andWhere('p.statut = :s')->setParameter('s',PublicationStatus::PUBLIE)->orderBy('p.createdAt','DESC')->setMaxResults($limit)->getQuery()->getResult(); }
    public function publishedByIds(array $ids): array { if(!$ids)return[]; $items=$this->createQueryBuilder('p')->andWhere('p.id IN (:ids)')->andWhere('p.statut = :s')->setParameter('ids',$ids)->setParameter('s',PublicationStatus::PUBLIE)->getQuery()->getResult(); $map=[]; foreach($items as $p){$map[$p->getId()]=$p;} return array_values(array_filter(array_map(fn($id)=>$map[$id]??null,$ids))); }
    public function fallbackSearch(string $q,int $limit=20): array { return $this->createQueryBuilder('p')->andWhere('p.statut = :s')->andWhere('LOWER(p.titre) LIKE :q OR LOWER(p.contenu) LIKE :q')->setParameter('s',PublicationStatus::PUBLIE)->setParameter('q','%'.mb_strtolower($q).'%')->orderBy('p.createdAt','DESC')->setMaxResults($limit)->getQuery()->getResult(); }
}
