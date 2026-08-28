<?php
declare(strict_types=1);
namespace App\Repository;
use App\Entity\User;
use App\Entity\WebauthnCredential;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\Bundle\Repository\DoctrineCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
final class WebauthnCredentialRepository extends DoctrineCredentialSourceRepository
{
    public function __construct(ManagerRegistry $registry,private UserRepository $users){parent::__construct($registry,WebauthnCredential::class);}
    public function saveCredentialSource(PublicKeyCredentialSource $source):void{if(!$source instanceof WebauthnCredential){$source=new WebauthnCredential($source->publicKeyCredentialId,$source->type,$source->transports,$source->attestationType,$source->trustPath,$source->aaguid,$source->credentialPublicKey,$source->userHandle,$source->counter);} $user=$this->users->find((int)$source->userHandle);if(!$user instanceof User)throw new \RuntimeException('Utilisateur WebAuthn introuvable.');$source->setUser($user);parent::saveCredentialSource($source);}
}
