# Trello Clone Backend

Laravel 11 API that powers a Trello-style project management experience. The application ships with Sanctum authentication, realtime broadcasting over Pusher-compatible websockets, ordered drag-and-drop lists/cards, CSV imports, activity logging, and demo seed data.

## Features

- **Authentication** – SPA/token friendly login, registration, and `auth/me` endpoints provided by Laravel Sanctum.
- **Projects & Membership** – Owners can invite members and assign `owner`, `admin`, `member`, or `viewer` roles enforced through policies.
- **Boards, Lists, Cards** – Nested REST resources support CRUD operations, card assignment, attachments, and position updates for drag-and-drop workflows.
- **Realtime updates** – `ProjectBroadcast` events publish board/list/card changes to `projects.{id}` channels for live collaboration.
- **Activity feed** – Every mutation records an auditable activity entry retrievable via `/projects/{project}/activity`.
- **CSV import & file storage** – Bulk create cards from CSV uploads and manage attachments stored on the configured filesystem disk.
- **Comprehensive tests** – Feature tests cover authentication flows, permissions, CRUD, drag-and-drop ordering, CSV import, and broadcasting events.
- **API documentation** – See [`openapi.yaml`](../openapi.yaml) for the complete request/response contract.

## Local Development

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Use `php artisan test` to execute the feature tests. Storage assets are served via `php artisan storage:link`, and broadcasting defaults to the Pusher protocol (compatible with Laravel WebSockets).

## Environment Highlights

Key `.env` variables:

- `BROADCAST_CONNECTION` / `PUSHER_*` – Configure for Pusher or Laravel WebSockets.
- `SANCTUM_STATEFUL_DOMAINS` & `FRONTEND_URL` – Required for SPA session support.
- `FILESYSTEM_DISK` – Defaults to `public` for attachment storage.

For cPanel deployment guidance review [`DEPLOYMENT.md`](DEPLOYMENT.md).

## Testing

Feature test coverage is implemented using PHPUnit and Sanctum helpers:

```bash
php artisan test
```

The suite validates authentication, authorization, CRUD operations, CSV import handling, drag-and-drop ordering, and broadcasting event dispatches.
