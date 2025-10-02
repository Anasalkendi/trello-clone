# Deployment Notes (cPanel)

These steps document how to deploy the Laravel backend on a typical cPanel shared hosting environment. For an end-to-end Hostinger example (including the Vite frontend and automated Git pulls) see the repository-level [`DEPLOYMENT.md`](../DEPLOYMENT.md).

## Requirements

- PHP 8.2 or newer with BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML extensions enabled.
- Composer available on the server for dependency installation.
- MySQL 8 / MariaDB 10.4+ database for production usage.
- Node.js or a Pusher-compatible websocket service (for example, [Laravel WebSockets](https://beyondco.de/docs/laravel-websockets/basic-usage/installation) running on a separate process) to satisfy broadcasting.

## Initial Setup

1. **Upload code**: Push this repository to the cPanel account (e.g. via Git Version Control or SSH `git clone`). Place the Laravel application outside the public_html directory when possible and symlink the `public/` folder into `public_html`.
2. **Install dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan event:cache
   ```
3. **Environment**: Copy `.env.example` to `.env` and configure:
   - `APP_URL`, `APP_ENV`, `APP_DEBUG` (false for production)
   - Database credentials (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Sanctum SPA host list (`SANCTUM_STATEFUL_DOMAINS`) and `FRONTEND_URL`
   - Broadcasting driver (`BROADCAST_CONNECTION=pusher`) and associated keys. For self-hosted websockets set `PUSHER_HOST`, `PUSHER_PORT`, `PUSHER_SCHEME=http`.
   - `FILESYSTEM_DISK=public`
4. **Database**: Run migrations and seed the demo data:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```
5. **Scheduler / Queues**:
   - Add a cron entry in cPanel for `* * * * * /usr/local/bin/php /path/to/artisan schedule:run >> /dev/null 2>&1`.
   - For queues, either enable `QUEUE_CONNECTION=database` with the `queue:work` command in a background process (using cPanel’s “Application Manager” or `nohup php artisan queue:work --tries=3 &`) or switch to a managed queue service.
6. **Broadcasting**:
   - For Pusher, configure the dashboard keys in `.env` and ensure the JS client connects to the same cluster.
   - For Laravel WebSockets on the same host, install the package on the server, run `php artisan websockets:serve` under a separate daemon (Supervisor or cPanel process manager), and point the Pusher configuration (`PUSHER_HOST`, `PUSHER_PORT`) to the running service.
7. **File permissions**: Ensure the web server user can write to `storage/` and `bootstrap/cache/`.

## Post Deployment

- Rotate Sanctum tokens if `.env` secrets change.
- Review `storage/logs/laravel.log` regularly for failed jobs or broadcasting errors.
- Run `php artisan queue:restart` after deploying new code.
