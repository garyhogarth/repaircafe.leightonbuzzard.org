# AGENTS.md

This is the **v2 rewrite** of the Leighton Buzzard Repair Cafe app — a ground-up Laravel 12 /
Filament / Livewire 3 rebuild of the live v1 site, tracked by upstream milestone
["RepairCafe v2"](https://github.com/robeastwood/repaircafe.leightonbuzzard.org/milestone/5). It is
**not yet at feature parity with v1** and is not live — see "Known gaps" below before assuming
something missing is a bug. See [README.md](README.md) for setup and the domain model.

This file (`AGENTS.md`) is the source of truth for AI agents working in this repo, regardless of
which tool reads it. `CLAUDE.md`, if present locally, is a thin pointer here — this repo's
`.gitignore` deliberately excludes `CLAUDE.md` (along with `.claude`, `.cursor`, `.idea`, etc.), so
don't rely on it being present or committed; put anything that should reach every contributor/agent
in this file or under `agents/` instead.

Deeper topic-specific detail lives under [agents/](agents/):
- [agents/database.md](agents/database.md) — schema, roles/permissions, seeding
- [agents/design.md](agents/design.md) — Livewire/Volt/Flux/Tailwind conventions
- [agents/filament.md](agents/filament.md) — the two Filament panels (`/admin`, `/dashboard`) and
  the policy pattern that gates them

As you discover something worth remembering (a gotcha, a convention, a "why is it done this way"),
add it to the relevant topic file — create a new one under `agents/` if it doesn't fit an existing
topic, and link it from this list.

## Stack specifics

- PHP ^8.2, Laravel 12, Filament 4 (admin panels), Fortify (auth, not Jetstream), Spatie
  Laravel Permission (roles/permissions)
- Livewire 3 (not 2 — v3 API differs from v2), Livewire Volt 1.7 for single-file components, Flux UI
  2.x (**free tier only** — `livewire/flux`, not `livewire/flux-pro`; don't add Pro components
  without flagging it, since that needs a paid license)
- Tailwind CSS 4 (CSS-based config via `@theme` in `resources/css/app.css`, no `tailwind.config.js`)
- Vite 7, built via `laravel-vite-plugin`
- Testing: Pest 4 (`tests/Feature`, `tests/Unit`), not PHPUnit-style `Test` classes directly
- Laravel Boost (`laravel/boost`) is installed — an MCP-based AI tooling package for
  `claude_code`/`cursor` (see `boost.json`). No guideline packs are currently configured
  (`"guidelines": []`).
- No PHP/Composer available in some environments — check before assuming you can run `artisan` or
  `pest` directly; if unavailable, say so rather than guessing at output.

## Conventions

- **Indentation**: 4 spaces everywhere except YAML (2 spaces) — see `.editorconfig`.
- **Formatting**: No Prettier config exists on this branch (unlike v1) — don't assume Blade files
  get auto-formatted; PHP formatting is Pint only, no custom `pint.json`, so default Pint rules
  apply.
- **Models**: Plain Eloquent in `app/Models/`. `Item` uses soft deletes and a global scope
  (`HideSoftDeletedForNonSuperAdmins`) rather than relying on callers to remember `withTrashed()`
  checks — follow this pattern (a scope, not ad-hoc query filtering) for any other soft-deleted
  model that needs the same hiding behavior.
- **Item status/power**: plain string columns, not DB enums — valid values and their Filament
  color/icon come from `Item::statusOptions()` / `Item::statusDetails()` / `Item::powerOptions()` in
  `app/Models/Item.php`. Add new values there, not as a migration-level enum.
- **Policies**: One policy per model in `app/Policies/`, gating on a single Spatie permission
  (e.g. `EventPolicy` checks `manage-events` for everything except restore/forceDelete, which check
  `super-admin`) rather than per-ability permissions — see [agents/filament.md](agents/filament.md)
  for how these wire into the two admin panels.
- **Routes**: `routes/web.php` is currently minimal (static pages + Fortify auth) — most real
  functionality lives behind the two Filament panels, not public routes. See "Known gaps" below.

## Known gaps (as of 2026-09-19 — re-check before relying on this)

Confirmed by hands-on testing plus the open issues on
[milestone "RepairCafe v2"](https://github.com/robeastwood/repaircafe.leightonbuzzard.org/milestone/5)
(9 open, 0 closed): no public event listing/detail pages, no way for a fixer/volunteer to sign up to
an event, no check-in interface, no user dashboard beyond the bare Filament scaffold, global search
not wired up, item-notes UI unfinished. Note the DB schema for volunteering/fixing (`event_user`
table's `volunteer`/`fixer` columns — see [agents/database.md](agents/database.md)) already exists;
it's a missing route/UI, not a missing data model. **Check the milestone's open issues before
treating an apparent gap as a bug** — most of what "feels missing" is tracked, expected, and probably
already has an issue number.

## Before committing

- If you touched PHP: run `./vendor/bin/pint` and `php artisan test` (or `composer run test`) if PHP
  is available in your environment.
- If you touched JS/CSS/Blade: run `npm run build` to make sure Vite compiles cleanly. There's no
  Prettier here to run (see Conventions above).
- There is no CI configured on this branch yet, so these checks are the only safety net — don't skip
  them.

## Ground rules

- Don't invent deployment steps, hosting details, or environment values that aren't in this repo —
  say what's unknown rather than guessing.
- Don't add new third-party services/API integrations (payment, analytics, external APIs) without
  flagging it explicitly — small volunteer-run project, keep the dependency footprint deliberate.
  This includes Flux Pro components — flag before suggesting any, since they need a paid license.
- Prefer extending existing Filament/Livewire/Volt/Flux patterns over introducing a new frontend
  approach.
