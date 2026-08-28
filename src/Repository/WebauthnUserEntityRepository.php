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
    public function findOneByUserHandle(string $v):?PublicKeyCredentialUserEntity{return $this->convert($this->users->findOneBy(['webauthnUserHandle'=>$v]));}
    private function convert(?User $u):?PublicKeyCredentialUserEntity
    {
        if (!$u instanceof User || $u->getWebauthnUserHandle() === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity($u->getEmail(),$u->getWebauthnUserHandle(),$u->getPrenom().' '.$u->getNom());
    }
}
