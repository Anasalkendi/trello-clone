# Hostinger / cPanel Deployment Guide

This document explains how to publish the Laravel backend and Vite frontend to Hostinger (or any cPanel based) shared hosting with automatic deployments triggered from the `main` branch.

## 1. Prepare the hosting account

1. **Create subdomains**
   - `api.alawalapp.com` → document root: `/home/<cpanel-user>/public_html/api/public` (the deploy script publishes the full Laravel app in `/public_html/api` and the subdomain must point at its `public/` folder)
   - `app.alawalapp.com` → document root: `/home/<cpanel-user>/public_html/app`
2. **Install runtimes** (through Hostinger’s cPanel):
   - PHP 8.2 with the extensions listed in [`backend/DEPLOYMENT.md`](backend/DEPLOYMENT.md).
   - Composer (`/opt/cpanel/composer/bin/composer`).
   - Node.js 18 or newer (via Node Version Manager or Hostinger’s NodeJS selector). Note the full path to the `npm` binary, e.g. `/opt/alt/alt-nodejs18/root/usr/bin/npm`.
3. **Create Git deployment repository** in cPanel → *Git Version Control* → *Create*. Use this GitHub repository URL and keep the clone path (e.g. `/home/<user>/repos/trello-clone`). Set *Automatic Deployment on Commit* to **On**.

## 2. Configure automation variables

`deployment/cpanel/deploy.sh` expects a config file with per-account paths and binary locations. Create the file `~/.cpanel/trello-clone.env` on the server (matching the `CPANEL_DEPLOY_CONFIG` default) with the following contents:

```bash
# Paths
BACKEND_DEPLOY_PATH="$HOME/public_html/api"
FRONTEND_PUBLIC_PATH="$HOME/public_html/app"

# Executables (update if Hostinger uses different versions)
PHP_BIN="/opt/alt/php82/usr/bin/php"
COMPOSER_BIN="/opt/cpanel/composer/bin/composer"
NPM_BIN="/opt/alt/alt-nodejs18/root/usr/bin/npm"

# Laravel options
RUN_MIGRATIONS=true
RUN_SEEDERS=false

# Frontend build env (optional)
FRONTEND_ENV_FILE="$HOME/.cpanel/frontend.env"
```

Create the optional frontend env file so Vite knows where to reach the API:

```bash
cat > ~/.cpanel/frontend.env <<'ENV'
VITE_API_URL=https://api.alawalapp.com/api
ENV
```

## 3. One-time backend preparation

SSH into the server and run:

```bash
mkdir -p $HOME/public_html/api
cd $HOME/public_html/api
cp $HOME/repos/trello-clone/backend/.env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
# Update .env with production values (APP_URL, database, Redis, mail, Pusher, Sanctum, etc.)
```

Refer to [`backend/DEPLOYMENT.md`](backend/DEPLOYMENT.md) for full environment variable descriptions, database migration, queue, and websocket configuration.

## 4. One-time frontend preparation

```bash
mkdir -p $HOME/public_html/app
```

No additional files are needed — the deploy script will publish the Vite `dist/` output to this folder each time.

## 5. Enable automated deployments

With the Git repository already configured in cPanel, every push to `main` will execute `.cpanel.yml`, which calls `deployment/cpanel/deploy.sh` to:

1. Sync the Laravel source into `BACKEND_DEPLOY_PATH` without overwriting `.env` or persistent storage.
2. Install Composer dependencies and run framework cache optimisations.
3. Optionally run database migrations and seeders (controlled by `RUN_MIGRATIONS` and `RUN_SEEDERS`).
4. Build the frontend via `npm ci && npm run build`.
5. Publish the compiled assets into `FRONTEND_PUBLIC_PATH`.

Check deployment logs in **cPanel → Git Version Control → Manage → History** to confirm successful runs. Errors usually come from missing PHP/Node binaries or incorrect paths in the config file.

## 6. Post-deployment validation checklist

After the first automated deploy:

- Issue a quick request such as `curl -I https://api.alawalapp.com/api/auth/login` to confirm the API responds (a `422 Unprocessable Content` status is expected without credentials).
- Visit `https://app.alawalapp.com/` to ensure the SPA loads and network calls target the API subdomain defined in `VITE_API_URL`.
- Review `storage/logs/laravel.log` and the browser console for unexpected errors.
- Re-run `php artisan queue:work` or `supervisorctl restart` if you manage queue workers outside the deploy script.

Following these steps ensures a repeatable, push-triggered deployment pipeline entirely managed from cPanel.
