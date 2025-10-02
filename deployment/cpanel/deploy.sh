#!/bin/bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
CONFIG_FILE="${CPANEL_DEPLOY_CONFIG:-$HOME/.cpanel/trello-clone.env}"

if [[ -f "$CONFIG_FILE" ]]; then
  # shellcheck disable=SC1090
  source "$CONFIG_FILE"
fi

: "${BACKEND_DEPLOY_PATH:?Set BACKEND_DEPLOY_PATH in $CONFIG_FILE}"
: "${FRONTEND_PUBLIC_PATH:?Set FRONTEND_PUBLIC_PATH in $CONFIG_FILE}"

BACKEND_PUBLIC_PATH="${BACKEND_PUBLIC_PATH:-$BACKEND_DEPLOY_PATH/public}"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

BACKEND_SOURCE="$ROOT_DIR/backend"
FRONTEND_SOURCE="$ROOT_DIR/frontend"

log() {
  printf '\n[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

run() {
  log "Running: $*"
  "$@"
}

log "Laravel public directory target: $BACKEND_PUBLIC_PATH"

log "Syncing Laravel backend to $BACKEND_DEPLOY_PATH"
run mkdir -p "$BACKEND_DEPLOY_PATH"
run rsync -a --delete \
  --exclude='.env' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/app/public' \
  --exclude='storage/framework/cache/data' \
  --exclude='storage/framework/sessions' \
  --exclude='storage/logs' \
  "$BACKEND_SOURCE"/ "$BACKEND_DEPLOY_PATH"/

if [[ "$BACKEND_PUBLIC_PATH" != "$BACKEND_DEPLOY_PATH/public" ]]; then
  log "Ensuring document root proxy files in $BACKEND_PUBLIC_PATH"
  run mkdir -p "$BACKEND_PUBLIC_PATH"
  cat <<'PHP' >"$BACKEND_PUBLIC_PATH/index.php"
<?php

require __DIR__.'/public/index.php';
PHP
  cat <<'HTACCESS' >"$BACKEND_PUBLIC_PATH/.htaccess"
DirectoryIndex public/index.php
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS
fi

pushd "$BACKEND_DEPLOY_PATH" >/dev/null
  log "Installing Composer dependencies"
  run "$COMPOSER_BIN" install --no-dev --optimize-autoloader

  log "Running Laravel optimizations"
  run mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache
  run touch storage/logs/laravel.log
  run chmod -R ug+rw storage bootstrap/cache
  run "$PHP_BIN" artisan storage:link || true
  run "$PHP_BIN" artisan config:cache
  run "$PHP_BIN" artisan route:cache
  run "$PHP_BIN" artisan event:cache

  if [[ "${RUN_MIGRATIONS:-true}" == "true" ]]; then
    log "Running database migrations"
    run "$PHP_BIN" artisan migrate --force
  else
    log "Skipping migrations (RUN_MIGRATIONS=false)"
  fi

  if [[ "${RUN_SEEDERS:-false}" == "true" ]]; then
    log "Seeding database"
    run "$PHP_BIN" artisan db:seed --force
  fi

  log "Restarting queue workers (if any)"
  "$PHP_BIN" artisan queue:restart || true
popd >/dev/null

log "Building frontend"
pushd "$FRONTEND_SOURCE" >/dev/null
  CLEAN_FRONTEND_ENV=false
  if [[ -n "${FRONTEND_ENV_FILE:-}" && -f "$FRONTEND_ENV_FILE" ]]; then
    log "Copying frontend environment file"
    cp "$FRONTEND_ENV_FILE" .env.production.local
    CLEAN_FRONTEND_ENV=true
  fi

  run "$NPM_BIN" ci
  run "$NPM_BIN" run build

  if [[ "$CLEAN_FRONTEND_ENV" == true ]]; then
    rm -f .env.production.local
  fi
popd >/dev/null

log "Publishing frontend dist to $FRONTEND_PUBLIC_PATH"
run mkdir -p "$FRONTEND_PUBLIC_PATH"
run rsync -a --delete "$FRONTEND_SOURCE/dist"/ "$FRONTEND_PUBLIC_PATH"/

log "Deployment complete"
