# Compendium

Compendium est une bibliothèque personnelle publique pour publier des notes, des idées et des documents longs. Stack : PHP 8.4, Symfony 8.1, Doctrine/PostgreSQL 16, Twig, Elasticsearch et WebAuthn.

## Installation locale

Prérequis : Docker avec Compose.

```bash
cp .env.example .env.local
docker compose up --build
```

Application : <http://localhost:8080>. Santé : <http://localhost:8080/health>. Les seuls services sont ``php``, ``nginx``, ``database`` et ``elasticsearch``. Aucune infrastructure SMTP n’est requise et Elasticsearch n’est pas publié sur l’hôte.

## Passkeys

Il n’existe ni mot de passe, ni code ou lien envoyé par e-mail. L’inscription recueille prénom, nom, e-mail unique et code postal facultatif, puis le navigateur crée une passkey. La connexion est validée par ``web-auth/webauthn-symfony-bundle``.

Local :

```dotenv
WEBAUTHN_RP_ID=localhost
WEBAUTHN_RP_NAME=Compendium
WEBAUTHN_ORIGIN=http://localhost:8080
```

Production :

```dotenv
WEBAUTHN_RP_ID=falchero.fr
WEBAUTHN_RP_NAME=Compendium
WEBAUTHN_ORIGIN=https://falchero.fr
```

Une passkey est liée au RP ID. WebAuthn requiert HTTPS hors ``localhost``.

## Administration

La création d’un compte attribue uniquement ``ROLE_USER``. La promotion reste exclusivement en ligne de commande :

```bash
docker compose exec php php bin/console app:user:promote-admin utilisateur@example.fr
```

L’administration gère publications, thèmes, ordre de la une (maximum dix) et brouillons Drive.

## Recherche, langues et formats

Elasticsearch indexe le titre, le texte nettoyé, le thème, la langue et le type des seules publications publiées. Une recherche SQL dégradée prend le relais si nécessaire.

Les traductions sont des publications autonomes partageant un ``groupeTraductionId``. Aucune traduction n’est générée. Les formats ``texte_brut``, ``html_drive`` et ``editorjs_json`` sont isolés par ``ContentTextExtractor``. Editor.js n’est pas présenté comme actif : son éditeur et son renderer structuré restent une étape ultérieure.

## Google Drive optionnel

```dotenv
GOOGLE_DRIVE_ENABLED=0
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_FOLDER_ID=
```

Avec Drive désactivé, tout le site fonctionne. Les secrets vont uniquement dans ``.env.local`` ou le fichier externe de production. Le MVP fournit un cycle administré vers un brouillon, jamais une publication automatique ni une synchronisation temps réel.

## Migrations et production

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

``app:seed`` est réservé au développement et refuse ``APP_ENV=prod``.

``compose.prod.yaml`` construit ``docker/php/Dockerfile.prod``, charge les secrets depuis ``/etc/compendium/compendium.env``, n’expose que Nginx sur ``127.0.0.1:3031`` et garde PostgreSQL/Elasticsearch privés.

```bash
docker compose -f compose.prod.yaml up -d --build
```

Le fichier externe définit au minimum ``APP_SECRET``, ``DATABASE_URL``, ``POSTGRES_DB``, ``POSTGRES_USER``, ``POSTGRES_PASSWORD``, ``ELASTICSEARCH_URL`` et les variables ``WEBAUTHN_*``. L’entrypoint applique les migrations, échoue avec elles et ne lance jamais les seeds.

## Vérifications

```bash
composer validate
find src tests migrations -name '*.php' -print0 | xargs -0 -n1 php -l
php bin/console lint:twig templates
php bin/console lint:yaml config compose.yaml compose.prod.yaml
php bin/console doctrine:schema:validate
php bin/phpunit
./scripts/smoke.sh
./scripts/security-check.sh
```

## Architecture

- ``src/Entity`` : modèle métier et credentials WebAuthn multiples.
- ``src/Security`` : cérémonies passkey sans cryptographie maison.
- ``src/Service`` : extraction de texte, Elasticsearch et état Drive.
- ``src/Controller`` : public, profil, santé et administration.
- ``templates`` / ``public`` : interface éditoriale et adaptation WebAuthn navigateur.
- ``migrations`` : historique SQL immuable.
