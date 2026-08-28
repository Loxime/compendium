<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Entity\WebauthnCredential;
use App\Repository\UserRepository;
use App\Repository\WebauthnCredentialRepository;
use App\Repository\WebauthnUserEntityRepository;
use App\Security\PendingRegistrationStorage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\TrustPath\EmptyTrustPath;

final class WebauthnRegistrationTest extends WebTestCase
{
    public function testInvalidOptionsRequestFailsWithoutCreatingUser(): void
    {
        $client = static::createClient();
        $users = static::getContainer()->get(UserRepository::class);
        $before = $users->count([]);

        $client->request('POST', '/inscription/passkey/options', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['email' => 'invalide'], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(400);
        self::assertSame($before, $users->count([]));
    }

    public function testOptionsStorePendingRegistrationWithoutCreatingUser(): void
    {
        $client = static::createClient();
        $users = static::getContainer()->get(UserRepository::class);
        $before = $users->count([]);
        $email = 'pending-'.bin2hex(random_bytes(5)).'@example.test';

        $client->request('POST', '/inscription/passkey/options', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'prenom' => 'Ada', 'nom' => 'Lovelace', 'email' => $email, 'code_postal' => '75001',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();
        self::assertSame($before, $users->count([]));
        $pending = $client->getRequest()->getSession()->get('compendium.pending_passkey_registrations', []);
        self::assertCount(1, $pending);
        self::assertSame($email, reset($pending)['email']);
    }

    public function testExistingEmailIsRejectedWithoutAttachingCredential(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $email = 'existing-'.bin2hex(random_bytes(5)).'@example.test';
        $user = (new User())->setPrenom('Compte')->setNom('Existant')->setEmail($email);
        $em->persist($user);
        $em->flush();

        try {
            $client->request('POST', '/inscription/passkey/options', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
                'prenom' => 'Visiteur', 'nom' => 'Anonyme', 'email' => $email,
            ], JSON_THROW_ON_ERROR));
            self::assertResponseStatusCodeSame(400);
            $response = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
            self::assertStringContainsString('déjà utilisée', $response['errorMessage']);
            self::assertCount(0, $user->getPasskeys());
        } finally {
            $em->remove($user);
            $em->flush();
        }
    }

    public function testValidatedCredentialPersistenceCreatesUserAndCredentialAtomically(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $stack = new RequestStack();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $stack->push($request);
        $pending = new PendingRegistrationStorage($stack);
        $repository = new WebauthnCredentialRepository(
            $container->get(ManagerRegistry::class),
            $container->get(UserRepository::class),
            $pending,
            $em,
        );
        $handle = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $email = 'validated-'.bin2hex(random_bytes(5)).'@example.test';
        $pending->store($handle, ['email' => $email, 'prenom' => 'Grace', 'nom' => 'Hopper', 'code_postal' => null]);

        $repository->saveCredentialRecord(new CredentialRecord(
            random_bytes(32), 'public-key', [], 'none', EmptyTrustPath::create(), Uuid::v4(), random_bytes(77), $handle, 0,
        ));

        $user = $container->get(UserRepository::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);
        $credentials = $container->get(WebauthnCredentialRepository::class)->findBy(['user' => $user]);
        self::assertCount(1, $credentials);
        self::assertNotSame('', $user->getWebauthnUserHandle());
        self::assertSame($handle, $user->getWebauthnUserHandle());
        self::assertSame($user->getWebauthnUserHandle(), $credentials[0]->userHandle);
        self::assertNull($pending->get($handle));

        $em->remove($user);
        $em->flush();
    }

    public function testUnknownOpaqueHandleCannotAttachToExistingAccount(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $stack = new RequestStack();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $stack->push($request);
        $repository = new WebauthnCredentialRepository($container->get(ManagerRegistry::class), $container->get(UserRepository::class), new PendingRegistrationStorage($stack), $em);
        $before = $container->get(UserRepository::class)->count([]);

        try {
            $repository->saveCredentialRecord(new CredentialRecord(random_bytes(32), 'public-key', [], 'none', EmptyTrustPath::create(), Uuid::v4(), random_bytes(77), 'opaque-handle-without-session', 0));
            self::fail('An unknown registration handle must be rejected.');
        } catch (\RuntimeException $exception) {
            self::assertStringContainsString('expiré', $exception->getMessage());
        }
        self::assertSame($before, $container->get(UserRepository::class)->count([]));
    }

    public function testUserEntityRepositoryUsesStableOpaqueHandle(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $handle = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $user = (new User())->setPrenom('Alan')->setNom('Turing')->setEmail('handle-'.bin2hex(random_bytes(5)).'@example.test')->setWebauthnUserHandle($handle);
        $em->persist($user);
        $em->flush();

        try {
            $repository = $container->get(WebauthnUserEntityRepository::class);
            $byHandle = $repository->findOneByUserHandle($handle);
            $byEmail = $repository->findOneByUsername($user->getEmail());

            self::assertNotNull($byHandle);
            self::assertSame($user->getEmail(), $byHandle->name);
            self::assertSame($handle, $byHandle->id);
            self::assertNotNull($byEmail);
            self::assertSame($handle, $byEmail->id);
            self::assertNotSame((string) $user->getId(), $byEmail->id);
            self::assertSame('Alan Turing', $byEmail->displayName);
            self::assertNull($repository->findOneByUserHandle('unknown-opaque-handle'));
        } finally {
            $em->remove($user);
            $em->flush();
        }
    }

    public function testLegacyUserWithoutHandleIsNotWebauthnAuthentifiableByEmail(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $user = (new User())->setPrenom('Ancien')->setNom('Compte')->setEmail('legacy-'.bin2hex(random_bytes(5)).'@example.test');
        $em->persist($user);
        $em->flush();

        try {
            $repository = $container->get(WebauthnUserEntityRepository::class);
            self::assertNull($repository->findOneByUsername($user->getEmail()));
            self::assertNull($repository->findOneByUserHandle((string) $user->getId()));
        } finally {
            $em->remove($user);
            $em->flush();
        }
    }
}
