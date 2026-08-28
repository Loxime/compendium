<?php
namespace App\Command;

use App\Entity\FeaturedPublication;
use App\Entity\Language;
use App\Entity\Publication;
use App\Entity\Theme;
use App\Entity\User;
use App\Enum\ContentFormat;
use App\Enum\PublicationStatus;
use App\Enum\PublicationType;
use App\Repository\LanguageRepository;
use App\Repository\PublicationRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:'app:seed',description:'Seed idempotent local demo content')]
final class SeedCommand extends Command
{
    public function __construct(private EntityManagerInterface $em,private UserRepository $users,private ThemeRepository $themes,private PublicationRepository $publications,private LanguageRepository $languages,private string $kernelEnvironment){parent::__construct();}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        if($this->kernelEnvironment==='prod'){$output->writeln('<error>app:seed est interdit en production.</error>');return Command::FAILURE;}
        $adminEmail=$_ENV['LOCAL_ADMIN_EMAIL']??'admin@compendium.local';
        if(!$this->users->findOneBy(['email'=>$adminEmail])){$u=(new User())->setPrenom('Admin')->setNom('Local')->setEmail($adminEmail)->setRoles(['ROLE_USER','ROLE_ADMIN']);$this->em->persist($u);}
        foreach([['fr','Français'],['en','English']] as [$code,$nom])if(!$this->languages->findOneBy(['code'=>$code])){$l=(new Language())->setCode($code)->setNom($nom);$this->em->persist($l);}
        $themeData=[['Technologie','technologie','fa-solid fa-microchip',1],['Idées','idees','fa-solid fa-lightbulb',2],['Archives','archives','fa-solid fa-box-archive',3]];
        foreach($themeData as [$nom,$slug,$icon,$ordre])if(!$this->themes->findOneBy(['slug'=>$slug])){$t=(new Theme())->setNom($nom)->setSlug($slug)->setIconeFontawesome($icon)->setOrdre($ordre);$this->em->persist($t);}
        $this->em->flush();
        if($this->publications->count([])===0){$tech=$this->themes->findOneBy(['slug'=>'technologie']);$ideas=$this->themes->findOneBy(['slug'=>'idees']);$samples=[[$tech,PublicationType::NOTE,'Bienvenue sur Compendium','Compendium rassemble des notes, idées et documents organisés par thèmes. Ce contenu de démonstration peut être supprimé depuis l’administration.'],[$ideas,PublicationType::IDEE,'Une plateforme pour publier ce qui mérite de durer','Le MVP privilégie une lecture simple, une recherche rapide et une modération administrateur.'],[$tech,PublicationType::DOC,'Architecture du MVP','Symfony 8.1, Twig, PostgreSQL, Elasticsearch et Docker Compose forment le socle local de cette première version.']];$pos=1;foreach($samples as [$theme,$type,$title,$body]){$p=(new Publication())->setTheme($theme)->setType($type)->setTitre($title)->setContenu($body)->setContenuFormat(ContentFormat::TEXTE_BRUT)->setStatut(PublicationStatus::PUBLIE);$this->em->persist($p);$this->em->flush();if($pos<=2){$f=(new FeaturedPublication())->setPublication($p)->setPosition($pos++);$this->em->persist($f);}}}
        $this->em->flush();$output->writeln('<info>Données locales prêtes.</info>');return Command::SUCCESS;
    }
}
