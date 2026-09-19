# Database

Detail notes for AI agents working on schema, migrations, roles/permissions, or local DB setup.
Linked from [AGENTS.md](../AGENTS.md).

## Local connection

- `.env.example` defaults to `DB_CONNECTION=sqlite` with no `DB_DATABASE` set (Laravel 12 default —
  it resolves to `database/database.sqlite`, created automatically by the `composer create-project`
  post-install script). This is enough for local dev.
- README's documented production/full-featured setup uses MySQL — if you switch, `composer.json`'s
  `require-dev` doesn't pull in a driver beyond what PHP already ships, just update `DB_*` in
  `.env` as normal.
- No `docker-compose.yml` exists on this branch despite Sail (`laravel/sail`) being a dev
  dependency and the README mentioning `sail up -d` — Sail hasn't been initialized here yet
  (`php artisan sail:install` would be needed first). Don't assume Sail containers exist without
  checking.

## Schema

- Migrations live in `database/migrations`. Core tables: `events`, `venues`, `items`, `categories`,
  `skills`, `notes`, `users` (extended with two-factor columns via Fortify), plus Spatie's
  permission tables (`create_permission_tables`).
- The `2025_10_11_184206_event_skill_user_pivots` migration (despite its name) creates **two**
  separate pivot tables, not one three-way pivot: `event_user` (`volunteer` and `fixer` nullable
  booleans — this is already the exact mechanism needed for "a user attends/volunteers/fixes at an
  event," it's just not wired up to any route/UI yet, see AGENTS.md's "Known gaps") and `skill_user`
  (plain `skill_id`/`user_id`, a user's skills).
- `Item` ↔ `Event` is a `belongsToMany` through the custom pivot model `EventItem`
  (`app/Models/EventItem.php`), backed by the `event_item` table: `repairer_id` (nullable, who's
  fixing it) and `checkedin` (a nullable **datetime**, not a boolean — despite the pivot casting it
  to `datetime`, `Item::checkedin()` filters with `wherePivot('checkedin', true)`, which works
  because a non-null datetime is truthy in the query, not because the column is boolean). A unique
  constraint on `(event_id, item_id)` was added in a later migration — one item can only be booked
  into a given event once. Use `Item::events()` / `Item::checkedin()` rather than querying the pivot
  table directly.
- `items.status` and `items.powered` are plain string columns, not DB enums — see AGENTS.md's
  Conventions section for where valid values live in PHP.
- `Item` uses soft deletes plus a global scope (`HideSoftDeletedForNonSuperAdmins`,
  `app/Models/Scopes/`) that hides trashed rows from anyone without the `super-admin` permission —
  this runs automatically on every query, so don't add manual `whereNull('deleted_at')` checks on
  top of it.

## Roles & permissions (Spatie Laravel Permission)

- Three roles, seeded by `RoleSeeder`: `admin` (every permission), `fixer` (`update-item-status`,
  `add-skills`, `add-notes`, `can-fix`), `volunteer` (`item-check-in`, `update-item-status`,
  `add-notes`, `can-volunteer`). Permissions themselves are seeded first by `PermissionSeeder`
  (21 permissions, e.g. `manage-events`, `access-admin-panel`, `super-admin`) — `RoleSeeder` depends
  on that seeder having already run, so keep `DatabaseSeeder`'s call order
  (`PermissionSeeder` → `RoleSeeder` → ... → `UserSeeder`).
- `super-admin` is a **permission**, not a role — policies check `$user->can('super-admin')`
  directly (see [agents/filament.md](filament.md)) rather than a `hasRole('super-admin')` check, so
  granting it means giving that specific permission to whichever role/user needs it, not creating a
  new role.

## v1 -> v2 data migration

- `php artisan migrate:from-v1` (`app/Console/Commands/MigrateFromV1.php`) copies data from the
  live v1 (Laravel 9) database into this v2 database, over a second `legacy` connection
  (`LEGACY_DB_*` in `.env`, see `.env.example`). Most tables are a straight row copy — v1 and v2
  share the same shapes for `venues`, `categories`, `skills`, `events`, `items`, `notes`,
  `event_user`, `skill_user`. Two tables need transformation, not a copy: `users` (v1's
  `is_admin`/`volunteer`/`fixer` boolean columns become Spatie role assignments in
  `model_has_roles`; run `PermissionSeeder`/`RoleSeeder` first) and `event_item` (v2 added a
  unique `(event_id, item_id)` constraint v1 never had, so duplicates are deduplicated, keeping
  whichever row has a `repairer_id`/`checkedin` set).
- Password hashes copy across unchanged — both apps use bcrypt (v1 explicitly via
  `config/hashing.php`, v2 via the Laravel default), so existing v1 users can log in with their
  current password after migration.
- Sessions are **not** migrated (`--fresh` wipes domain tables, not `sessions`/`cache`/`jobs`) —
  v2 has its own `APP_KEY`, so old session payloads/cookies can't decrypt there regardless.
  Everyone logs back in after cutover; this is expected, not a bug.
- Run with `--dry-run` first to see legacy row counts, `--fresh` to wipe this app's domain tables
  before importing (needed for a second run), and inspect the docblocks on each `copy*` method for
  the one-off column mapping it does.

## Seeding

- `database/seeders/DatabaseSeeder.php` runs, in order: `PermissionSeeder`, `RoleSeeder`,
  `VenueSeeder`, `SkillSeeder`, `CategorySeeder`, `UserSeeder`, `ItemSeeder`, `EventSeeder`,
  `NoteSeeder`. Run `php artisan db:seed` after migrating to get a usable local dataset.
- `UserSeeder` creates three fixed test accounts (all password `password`): `test@test.com` (admin),
  `fixer@test.com` (fixer), `volunteer@test.com` (volunteer) — plus 10 random factory users. Use
  these directly; don't manually `assignRole()` via Tinker unless testing a new role.
