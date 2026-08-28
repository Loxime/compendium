<?php
declare(strict_types=1);
namespace App\Security;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\Bundle\Security\Guesser\UserEntityGuesser;
use Webauthn\PublicKeyCredentialUserEntity;
final class RegistrationUserEntityGuesser implements UserEntityGuesser
{
    public function __construct(private UserRepository $users, private PendingRegistrationStorage $pendingRegistrations)
    {
    }

    public function findUserEntity(Request $request): PublicKeyCredentialUserEntity
    {
        $payload = $request->getPayload();
        $email = mb_strtolower(trim((string) $payload->get('email')));
        $prenom = trim((string) $payload->get('prenom'));
        $nom = trim((string) $payload->get('nom'));
        $codePostal = trim((string) $payload->get('code_postal')) ?: null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $prenom === '' || $nom === ''
            || mb_strlen($email) > 180 || mb_strlen($prenom) > 100 || mb_strlen($nom) > 100
            || ($codePostal !== null && mb_strlen($codePostal) > 20)) {
            throw new \InvalidArgumentException('Prénom, nom et adresse e-mail valides sont obligatoires.');
        }
        if ($this->users->findOneBy(['email' => $email]) instanceof User) {
            throw new \DomainException('Cette adresse e-mail est déjà utilisée.');
        }

        $userHandle = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->pendingRegistrations->store($userHandle, [
            'email' => $email,
            'prenom' => $prenom,
            'nom' => $nom,
            'code_postal' => $codePostal,
        ]);

        return new PublicKeyCredentialUserEntity($email, $userHandle, $prenom.' '.$nom);
    }
}
