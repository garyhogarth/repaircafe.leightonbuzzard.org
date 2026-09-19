# Database

Detail notes for AI agents working on schema, migrations, or local DB setup. Linked from
[CLAUDE.md](../CLAUDE.md) / [AGENTS.md](../AGENTS.md).

## Local connection

- Engine: MySQL 8.0 (see `docker-compose.yml` / `mysql/mysql-server:8.0`).
- **`DB_HOST` depends on how you're running the app**. `.env.example` defaults to `DB_HOST=mysql`
  (the Sail/Docker Compose service name, matching `MAIL_HOST=mailpit` already in that file), since
  Sail is the recommended path — see README's Option A.
  - Laravel Sail: keep `DB_HOST=mysql` as-is.
  - Native PHP (`php artisan serve` talking to MySQL on your host): change it to `DB_HOST=127.0.0.1`
    (or wherever your MySQL actually runs) — see README's Option B.
  - Discovered: 2026-09-18, first Sail setup in this repo. Originally `.env.example` defaulted to
    `127.0.0.1`, which breaks Sail with `SQLSTATE[HY000] [2002] Connection refused` (inside the
    `laravel.test` container, `127.0.0.1` is the container itself, not the `mysql` service) — fixed
    by flipping the default rather than documenting a workaround.
- `DB_DATABASE` defaults to `repaircafe.leightonbuzzard.org` (matches the repo name). `DB_PASSWORD`
  is empty locally; `mysql`'s `MYSQL_ALLOW_EMPTY_PASSWORD=1` in `docker-compose.yml` permits this.

## Schema

- Migrations live in `database/migrations`, plain Eloquent naming (no custom migration squashing).
- Core tables: `events`, `venues`, `items`, `categories`, `skills`, `notes`, `users` (Jetstream's
  default users table extended with an `is_admin` flag and fixer-related columns), plus pivot
  tables for `event_item` (item ↔ event bookings, with `repairer_id` / `checkedin` pivot columns)
  and user↔skill.
- `items.status` is a plain string column, not a DB enum — valid values are defined in PHP via
  `Item::statusOptions()` (`app/Models/Item.php`), not a migration-level `ENUM` or a Laravel enum
  class. If you add a new status, update that method (and the matching Blade component,
  `resources/views/components/pill-status.blade.php`) together.
- `Item` uses soft deletes (`SoftDeletes` trait); `Note` does not.
- A rough ER diagram exists at `database-design.xml` / `docs/sql_designer.xml` (SQL Designer XML
  format) — may drift from the actual migrations, treat as a rough guide only, not authoritative.

## Seeding

- `database/seeders` has one seeder per core table (`VenueSeeder`, `SkillSeeder`,
  `CategorySeeder`, `UserSeeder`, `ItemSeeder`, `EventSeeder`), run in that order from
  `DatabaseSeeder`. Run `artisan migrate --seed` (or `sail artisan migrate --seed`) to get a usable
  local dataset — an empty DB has no venues/categories/skills, so item/event creation forms will
  have nothing to select without seeding first.
