#!/bin/sh
set -eu
cd /var/www/html

# The source tree is bind-mounted in dev, while vendor lives in its own Docker volume.
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

# The development source tree is bind-mounted while vendor is persisted in a
# Docker volume. Refresh the authoritative classmap so newly added App classes
# are loadable without rebuilding or deleting that volume.
composer dump-autoload --classmap-authoritative --no-interaction

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
php bin/console app:seed --no-interaction
php bin/console app:search:reindex --no-interaction || true

exec "$@"
