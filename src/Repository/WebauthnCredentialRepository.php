<?php
declare(strict_types=1);
namespace App\Repository;
use App\Entity\User;
use App\Entity\WebauthnCredential;
use App\Security\PendingRegistrationStorage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\Bundle\Repository\CanSaveCredentialRecord;
use Webauthn\Bundle\Repository\CredentialRecordRepositoryInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

/** @extends ServiceEntityRepository<WebauthnCredential> */
final class WebauthnCredentialRepository extends ServiceEntityRepository implements CredentialRecordRepositoryInterface, PublicKeyCredentialSourceRepositoryInterface, CanSaveCredentialRecord
{
    public function __construct(
        ManagerRegistry $registry,
        private UserRepository $users,
        private PendingRegistrationStorage $pendingRegistrations,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function saveCredentialRecord(CredentialRecord $record): void
    {
        $this->save($record);
    }

    /** @return array<CredentialRecord> */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return $this->createQueryBuilder('credential')
            ->where('credential.userHandle = :userHandle')
            ->setParameter('userHandle', $publicKeyCredentialUserEntity->id)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?CredentialRecord
    {
        return $this->createQueryBuilder('credential')
            ->where('credential.publicKeyCredentialId = :credentialId')
            ->setParameter('credentialId', base64_encode($publicKeyCredentialId))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function save(CredentialRecord $record): void
    {
        $pending = $this->pendingRegistrations->get($record->userHandle);

        $this->entityManager->wrapInTransaction(function () use ($record, $pending): void {
            $user = null;
            if ($pending !== null) {
                if ($this->users->findOneBy(['email' => $pending['email']]) instanceof User) {
                    throw new \DomainException('Cette adresse e-mail est déjà utilisée.');
                }
                $user = (new User())
                    ->setEmail($pending['email'])
                    ->setPrenom($pending['prenom'])
                    ->setNom($pending['nom'])
                    ->setCodePostal($pending['code_postal'])
                    ->setWebauthnUserHandle($record->userHandle);
                $this->entityManager->persist($user);
            } else {
                $user = $this->users->findOneBy(['webauthnUserHandle' => $record->userHandle]);
            }

            if (!$user instanceof User) {
                throw new \RuntimeException('La cérémonie d’inscription a expiré. Veuillez recommencer.');
            }

            $credential = $record instanceof WebauthnCredential ? $record : new WebauthnCredential(
                $record->publicKeyCredentialId,
                $record->type,
                $record->transports,
                $record->attestationType,
                $record->trustPath,
                $record->aaguid,
                $record->credentialPublicKey,
                $record->userHandle,
                $record->counter,
            );
            $credential->setUser($user);
            $this->entityManager->persist($credential);
            $this->entityManager->flush();
        });

        if ($pending !== null) {
            $this->pendingRegistrations->remove($record->userHandle);
        }
    }
}
