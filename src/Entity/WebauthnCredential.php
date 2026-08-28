<?php
declare(strict_types=1);
namespace App\Entity;
use App\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;
#[ORM\Entity(repositoryClass: WebauthnCredentialRepository::class)]
#[ORM\Table(name: 'webauthn_credentials')]
class WebauthnCredential extends PublicKeyCredentialSource
{
    #[ORM\Id, ORM\Column(length: 26, unique: true)] private string $id;
    #[ORM\ManyToOne(inversedBy: 'passkeys'), ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private ?User $user = null;
    #[ORM\Column(length: 100)] private string $name = 'Passkey';
    #[ORM\Column] private \DateTimeImmutable $createdAt;
    public function __construct(string $publicKeyCredentialId, string $type, array $transports, string $attestationType, TrustPath $trustPath, AbstractUid $aaguid, string $credentialPublicKey, string $userHandle, int $counter) { $this->id=(string)new Ulid(); $this->createdAt=new \DateTimeImmutable(); parent::__construct($publicKeyCredentialId,$type,$transports,$attestationType,$trustPath,$aaguid,$credentialPublicKey,$userHandle,$counter); }
    public function getId():string{return $this->id;} public function getUser():?User{return $this->user;} public function setUser(User $v):static{$this->user=$v;return $this;} public function getName():string{return $this->name;} public function setName(string $v):static{$this->name=trim($v)?:'Passkey';return $this;} public function getCreatedAt():\DateTimeImmutable{return $this->createdAt;}
}
