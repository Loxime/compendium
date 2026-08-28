#!/bin/sh
set -eu
cd /var/www/html
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
exec "$@"
