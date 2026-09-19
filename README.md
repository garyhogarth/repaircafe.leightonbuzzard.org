# Repair Cafe Leighton Buzzard

A website & event management app for the repair cafe.

https://repaircafe.leightonbuzzard.org

## What this is

A [Laravel 9](https://laravel.com/docs/9.x) app (PHP, [Jetstream](https://jetstream.laravel.com/) +
[Livewire 2](https://laravel-livewire.com/) for the UI, [Tailwind CSS](https://tailwindcss.com/) +
[Alpine.js](https://alpinejs.dev/) via [Vite](https://vitejs.dev/) for the frontend) that runs the
core domain of the repair cafe:

- **Events** — repair cafe sessions, held at a **Venue**
- **Items** — things people bring in to be fixed, with a status (`broken` → `assessed` → `fixed` /
  `awaitingparts` / `unfixable`), a **Category**, and **Notes** left by volunteers
- **Users** — volunteers and admins, with **Skills**, who check in to events and repair items

See `app/Models` and `database/migrations` for the full schema.

## Prerequisites

You need one of the two setups below. Either way you'll also want [Docker
Desktop](https://www.docker.com/products/docker-desktop/) (`brew install --cask docker`) if you're
using Sail — open it once after installing so its daemon starts.

- **Sail / Docker-only** (recommended if you don't already have PHP set up): just Docker Desktop.
  No PHP or Composer needed on your machine — see Option A.
- **Native PHP**: PHP ^8.0.2, [Composer](https://getcomposer.org/), and a MySQL database, installable
  via Homebrew (`brew install php composer`) — see Option B.

Both options need [Node 18+](https://nodejs.org/) for the frontend build.

## Getting started

### Option A — Laravel Sail (Docker), no local PHP required

Sail (Laravel's Docker dev environment) is already declared as a dependency in `composer.json`, but
you need `vendor/` populated before you can run `sail` itself — do that with a throwaway PHP+Composer
container so you never need PHP installed locally:

```bash
cp .env.example .env

docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php82-composer:latest \
  composer install --ignore-platform-reqs

./vendor/bin/sail up -d
sail artisan key:generate
sail artisan migrate --seed
sail npm install
sail npm run dev
```

The app runs at `http://localhost`. Mailpit (for catching outgoing mail locally) is at
`http://localhost:8025`. From here on, prefix artisan/composer/npm commands with `sail` (e.g.
`sail artisan tinker`, `sail composer require ...`) so they run inside the container.

### Option B — Local PHP/Node

`.env.example` defaults `DB_HOST` to `mysql` and `MAIL_HOST` to `mailpit` — Docker Compose service
names that only resolve inside Sail's network. Running natively, point these at your own MySQL/mail
setup instead (typically `DB_HOST=127.0.0.1`).

```bash
cp .env.example .env
composer install
php artisan key:generate
# in .env: set DB_HOST=127.0.0.1 (or your DB host) and other DB_* for a MySQL database you've created
php artisan migrate --seed
npm install
npm run dev   # in a separate terminal from `php artisan serve`
php artisan serve
```

### Test users

`migrate --seed` (both options above) creates login accounts via `database/seeders/UserSeeder.php`,
all with password `password`:

- **Admin**: `test@test.com` — `is_admin = true`, can access `/admin`
- **Guest**: `guest@test.com` — plain user, no admin/volunteer flags
- **Volunteer**: `volunteer@test.com` — `volunteer = true`, with 5 random skills assigned
- 20 further random guest users (faker-generated names/emails)
- 20 further random volunteers, each with 5 random skills assigned

All seeded accounts have `email_verified_at` set, so no email verification step is needed to log in.

### Tests

```bash
php artisan test
# or: ./vendor/bin/phpunit
```

## Deployment

Not currently documented in this repo — there's no CI/CD config or hosting-specific setup checked
in, only the local Sail `docker-compose.yml`. If you know how/where the live site
(repaircafe.leightonbuzzard.org) is deployed, please add it here.

## Working with AI coding assistants

This repo has [`CLAUDE.md`](CLAUDE.md) and [`AGENTS.md`](AGENTS.md) documenting conventions and
ground rules for AI coding agents working in this codebase. Keep them up to date as conventions
change.

## License

MIT — see [LICENSE](LICENSE).
