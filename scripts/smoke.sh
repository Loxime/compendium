#!/bin/sh
set -eu
BASE_URL="${BASE_URL:-http://localhost:8080}"
printf 'Homepage... '
curl -fsS "$BASE_URL/" >/dev/null && echo OK
printf 'Health... '
curl -fsS "$BASE_URL/health" && echo
printf 'Login page... '
curl -fsS "$BASE_URL/connexion" >/dev/null && echo OK
printf 'Elasticsearch... '
docker compose exec -T elasticsearch curl -fsS http://localhost:9200/_cluster/health >/dev/null && echo OK
