.PHONY: up down reset logs shell check security
up:
	docker compose up --build -d

down:
	docker compose down

reset:
	docker compose down -v
	docker compose up --build -d

logs:
	docker compose logs -f php nginx

shell:
	docker compose exec php sh

check:
	docker compose exec php php bin/console doctrine:schema:validate
	./scripts/smoke.sh

security:
	./scripts/security-check.sh
