<?php
declare(strict_types=1);
namespace App\Repository;
use App\Entity\User;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;
final class WebauthnUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(private UserRepository $users){}
    public function findOneByUsername(string $v):?PublicKeyCredentialUserEntity{return $this->convert($this->users->findOneBy(['email'=>mb_strtolower(trim($v))]));}
    public function findOneByUserHandle(string $v):?PublicKeyCredentialUserEntity{return $this->convert($this->users->find((int)$v));}
    private function convert(?User $u):?PublicKeyCredentialUserEntity{return $u?new PublicKeyCredentialUserEntity($u->getEmail(),(string)$u->getId(),$u->getPrenom().' '.$u->getNom()):null;}
}
