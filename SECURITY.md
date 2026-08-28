# Politique de sécurité du dépôt public

Ce projet est destiné à être hébergé dans un dépôt GitHub public.

## Ne jamais committer

- `.env.local` et variantes `*.local`
- tokens API / OAuth
- mots de passe réels
- secrets de webhook
- clés privées SSH/TLS
- credentials Google Drive
- exports de bases de données réelles
- dumps ou logs contenant des données personnelles

Les valeurs présentes dans `.env` et `compose.yaml` sont exclusivement des valeurs de démonstration locale, non réutilisables en production.

## En cas de secret committé par erreur

Considérer le secret comme compromis : le révoquer/faire tourner immédiatement. Supprimer le secret de l'historique Git ne suffit pas à le rendre sûr.
