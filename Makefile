.PHONY: setup up down dev test backend-test frontend-test build format logs jobs

setup:
	./scripts/setup.sh

up:
	docker compose up -d --wait --wait-timeout 90 api

down:
	docker compose --profile jobs down

dev:
	npm run dev --prefix frontend

test: backend-test frontend-test

backend-test:
	docker compose run --rm api vendor/bin/phpunit

frontend-test:
	npm test --prefix frontend

build:
	npm run build --prefix frontend

format:
	docker compose run --rm api vendor/bin/pint --dirty --format agent

logs:
	docker compose logs -f api

jobs:
	docker compose --profile jobs up -d api worker scheduler
