<?php
declare(strict_types=1);
namespace App\Security;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\Bundle\Security\Guesser\UserEntityGuesser;
use Webauthn\PublicKeyCredentialUserEntity;
final class RegistrationUserEntityGuesser implements UserEntityGuesser
{
    public function __construct(private UserRepository $users,private EntityManagerInterface $em){}
    public function findUserEntity(Request $r):PublicKeyCredentialUserEntity{$email=mb_strtolower(trim((string)$r->request->get('email')));$prenom=trim((string)$r->request->get('prenom'));$nom=trim((string)$r->request->get('nom'));if(!filter_var($email,FILTER_VALIDATE_EMAIL)||$prenom===''||$nom==='')throw new \InvalidArgumentException('Prénom, nom et adresse e-mail valides sont obligatoires.');if($this->users->findOneBy(['email'=>$email]))throw new \DomainException('Cette adresse e-mail est déjà utilisée.');$u=(new User())->setEmail($email)->setPrenom($prenom)->setNom($nom)->setCodePostal(trim((string)$r->request->get('code_postal'))?:null);$this->em->persist($u);$this->em->flush();return new PublicKeyCredentialUserEntity($email,(string)$u->getId(),$prenom.' '.$nom);}
}
