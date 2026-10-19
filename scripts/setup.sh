#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

for executable in docker npm; do
  if ! command -v "$executable" >/dev/null 2>&1; then
    echo "Prérequis manquant : $executable" >&2
    exit 1
  fi
done
docker compose version >/dev/null

if [ ! -f .env ]; then
  cp .env.example .env
fi
if [ ! -f backend/.env ]; then
  cp backend/.env.example backend/.env
fi
if [ ! -f frontend/.env ]; then
  cp frontend/.env.example frontend/.env
fi

docker compose build api
docker compose run --rm api composer install --no-interaction --prefer-dist
if ! grep -Eq '^APP_KEY=.+$' backend/.env; then
  docker compose run --rm api php artisan key:generate --no-interaction
fi
docker compose run --rm api php artisan migrate --no-interaction
npm ci --prefix frontend
docker compose up -d --wait --wait-timeout 90 api

echo 'Fondations prêtes. Lancez : npm run dev --prefix frontend'
