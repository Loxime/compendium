# Compendium — MVP local Docker

Compendium est une plateforme de publication et d'archivage de notes, idées et documents. Ce dépôt contient un **MVP local** conçu pour être lancé avec Docker Compose et testé avant le premier commit public.

## Fonctionnalités incluses

- Symfony 8.1 + Twig
- PostgreSQL 16
- Elasticsearch 9.4.5 avec recherche full-text et fallback SQL
- Mailpit pour tester les liens magiques sans envoyer d'e-mail réel
- Publications : note / idée / document, brouillon / publié / archivé
- Thèmes administrables
- À la une (1 à 10 positions)
- Likes / dislikes uniques par utilisateur
- Avis ; après suppression d'un compte, l'avis reste sous `[user_deleted]`
- Suppression de compte en hard delete
- Admin local créé automatiquement
- Simulateur Google Drive : ajout/modification d'un document avec retour automatique au statut brouillon
- Endpoint de santé `/health`

> La synchronisation Google Drive réelle n'est volontairement **pas activée** dans ce premier ZIP : elle nécessite des identifiants Google. Aucun identifiant externe n'est fourni ou attendu pour tester le MVP.

## Prérequis

- Docker
- Docker Compose v2 (`docker compose`)
- Environ 2 Go de RAM disponible pour la stack locale (Elasticsearch utilise 512 Mo de heap dans cette configuration)

## Démarrage

```bash
unzip compendium-mvp.zip
cd compendium-mvp
docker compose up --build -d
```

Vérifier ensuite :

```bash
docker compose ps
curl http://localhost:8080/health
```

Accès locaux :

- Application : `http://localhost:8080`
- Mailpit : `http://localhost:8025`
- Elasticsearch : `http://localhost:9200`

## Connexion administrateur locale

Un compte de démonstration est créé au premier démarrage :

```text
admin@compendium.local
```

1. Ouvrir `http://localhost:8080/connexion`.
2. Saisir `admin@compendium.local`.
3. Cliquer sur « Recevoir mon lien ».
4. Ouvrir Mailpit sur `http://localhost:8025`.
5. Ouvrir le message reçu puis le lien magique.
6. Le menu **Admin** devient accessible.

Il n'y a aucun mot de passe de démonstration.

## Parcours de test conseillé

1. Vérifier les publications de démonstration sur l'accueil.
2. Effectuer une recherche (`architecture`, `plateforme`, etc.).
3. Se connecter avec le compte admin via Mailpit.
4. Créer un thème.
5. Créer une publication en brouillon puis la publier.
6. Ajouter la publication à la une.
7. Créer un second compte avec une adresse fictive et son lien Mailpit.
8. Tester like, dislike, changement d'avis et suppression de réaction.
9. Ajouter un avis.
10. Supprimer le second compte : l'avis doit afficher `[user_deleted]`.
11. Dans **Admin > Drive**, simuler deux imports avec le même `drive_file_id` : la publication concernée doit rester/repasser en brouillon.

## Commandes utiles

```bash
# Logs
docker compose logs -f php nginx

# Validation du schéma Doctrine
docker compose exec php php bin/console doctrine:schema:validate

# Voir les routes
docker compose exec php php bin/console debug:router

# Réindexer Elasticsearch
docker compose exec php php bin/console app:search:reindex

# Promouvoir manuellement un compte
docker compose exec php php bin/console app:user:promote-admin user@example.com

# Test HTTP automatique
./scripts/smoke.sh

# Arrêter
docker compose down

# Reset complet des données locales
docker compose down -v
```

## Avant le premier commit public

Le dépôt GitHub cible est public. Les règles suivantes sont obligatoires :

- ne jamais committer `.env.local` ni `.env.*.local` ;
- ne jamais committer de clé Google, token OAuth, webhook secret, mot de passe réel, clé API ou certificat privé ;
- conserver uniquement des valeurs locales factices dans `.env` / `compose.yaml` ;
- stocker les futurs secrets de développement dans `.env.local`, déjà ignoré par Git ;
- en CI/production, utiliser les **GitHub Actions Secrets** ou le gestionnaire de secrets de l'hébergeur ;
- contrôler le diff avant chaque push.

Commande de contrôle rapide :

```bash
git status
git diff --cached
```

Le projet est fourni sans `composer.lock`, car le ZIP est construit hors réseau. Après le premier démarrage réussi et **avant le premier commit**, générer le lock depuis le conteneur :

```bash
docker compose exec php composer update --lock --no-install
```

Puis vérifier qu'il ne contient évidemment aucun secret (un `composer.lock` normal ne doit contenir que des métadonnées de dépendances).

## Google Drive réel — étape suivante

Quand le MVP local sera validé, la vraie intégration Drive pourra être ajoutée. Les valeurs devront rester uniquement dans `.env.local` :

```dotenv
GOOGLE_DRIVE_ENABLED=1
GOOGLE_DRIVE_CLIENT_ID=...
GOOGLE_DRIVE_CLIENT_SECRET=...
GOOGLE_DRIVE_FOLDER_ID=...
```

Le fichier `.env.local` ne doit jamais être ajouté à Git.

## Architecture

```text
src/
├── Command/       # seed, promotion admin, réindexation
├── Controller/    # public, sécurité, profil et admin
├── Entity/        # modèle Doctrine
├── Enum/          # types/statuts/sources/réactions
├── Repository/
└── Service/       # extraction texte, Elasticsearch, état Drive

docker/
├── nginx/
└── php/

migrations/        # schéma PostgreSQL initial
templates/         # Twig
public/styles/     # UI du MVP
```

## Limites assumées de ce premier MVP

- l'éditeur riche Editor.js n'est pas encore intégré ; l'admin utilise un textarea pour fiabiliser le premier test ;
- le rendu `html_drive` est échappé côté public pour éviter toute injection HTML tant qu'un sanitizer n'est pas ajouté ;
- l'intégration Google Drive réelle (OAuth/API `changes.watch`) est remplacée par un simulateur de cycle métier ;
- le multilingue complet et son écran de fallback restent V2, conformément au cahier des charges ;
- aucune donnée de démonstration n'est destinée à la production.
