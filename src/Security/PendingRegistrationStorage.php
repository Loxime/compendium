<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RequestStack;

final readonly class PendingRegistrationStorage
{
    private const SESSION_KEY = 'compendium.pending_passkey_registrations';
    private const TTL = 600;

    public function __construct(private RequestStack $requestStack)
    {
    }

    /** @param array{email: string, prenom: string, nom: string, code_postal: ?string} $registration */
    public function store(string $userHandle, array $registration): void
    {
        $pending = $this->all();
        $this->removeExpired($pending);
        $pending[$userHandle] = $registration + ['expires_at' => time() + self::TTL];
        $this->requestStack->getSession()->set(self::SESSION_KEY, $pending);
    }

    /** @return array{email: string, prenom: string, nom: string, code_postal: ?string}|null */
    public function get(string $userHandle): ?array
    {
        $pending = $this->all();
        $this->removeExpired($pending);
        $this->requestStack->getSession()->set(self::SESSION_KEY, $pending);
        $registration = $pending[$userHandle] ?? null;

        if (!is_array($registration)) {
            return null;
        }

        unset($registration['expires_at']);

        return $registration;
    }

    public function remove(string $userHandle): void
    {
        $pending = $this->all();
        unset($pending[$userHandle]);
        $this->requestStack->getSession()->set(self::SESSION_KEY, $pending);
    }

    /** @return array<string, array<string, mixed>> */
    private function all(): array
    {
        $pending = $this->requestStack->getSession()->get(self::SESSION_KEY, []);

        return is_array($pending) ? $pending : [];
    }

    /** @param array<string, array<string, mixed>> $pending */
    private function removeExpired(array &$pending): void
    {
        $now = time();
        foreach ($pending as $handle => $registration) {
            if (!is_array($registration) || ($registration['expires_at'] ?? 0) <= $now) {
                unset($pending[$handle]);
            }
        }
    }
}
