# MVP status

## Inclus dans ce ZIP

- Stack Docker Compose : Nginx, PHP-FPM 8.4, PostgreSQL 16, Elasticsearch 9.4.5, Mailpit.
- Symfony 8.1 configuré manuellement pour un premier build reproductible.
- Modèle Doctrine complet du MVP.
- Migration initiale PostgreSQL.
- Seed local idempotent avec compte admin et contenus de démonstration.
- Magic-link via Symfony Security + Mailpit.
- Pages publiques, thèmes, recherche, publications, avis, réactions.
- Profil + hard delete.
- Administration des thèmes, publications et « à la une ».
- Simulateur de cycle Google Drive.
- Recherche Elasticsearch + fallback SQL.
- Scripts de smoke test et de contrôle de motifs de secrets.

## Contrôles effectués avant livraison

- syntaxe PHP : OK ;
- parsing JSON : OK ;
- parsing YAML : OK ;
- scan statique de motifs courants de secrets : OK ;
- dépôt GitHub `Loxime/compendium` vérifié vide avant toute écriture distante.

## Contrôle restant à faire sur la machine cible

L'environnement de génération de l'archive ne fournit pas Docker. Le test d'intégration réel doit donc être effectué sur la machine locale avec :

```bash
docker compose up --build -d
./scripts/smoke.sh
docker compose exec php php bin/console doctrine:schema:validate
```

Le premier commit/push ne doit être réalisé qu'après ces contrôles.
