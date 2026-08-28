<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the stable WebAuthn user handle and backfill only users whose credentials share one unambiguous handle.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD webauthn_user_handle VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE users u SET webauthn_user_handle = handles.user_handle FROM (SELECT credentials.user_id, MIN(credentials.user_handle) AS user_handle FROM webauthn_credentials credentials GROUP BY credentials.user_id HAVING COUNT(DISTINCT credentials.user_handle) = 1 AND COUNT(DISTINCT credentials.user_handle) FILTER (WHERE credentials.user_handle IN (SELECT shared.user_handle FROM webauthn_credentials shared GROUP BY shared.user_handle HAVING COUNT(DISTINCT shared.user_id) = 1)) = 1) handles WHERE handles.user_id = u.id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D13D977A ON users (webauthn_user_handle)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1483A5E9D13D977A');
        $this->addSql('ALTER TABLE users DROP webauthn_user_handle');
    }
}
