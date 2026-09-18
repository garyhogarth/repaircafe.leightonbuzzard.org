# CLAUDE.md

Guidance for Claude Code (and other AI agents — see [AGENTS.md](AGENTS.md)) working in this repo.

## What this app is

Laravel 9 app for running the Leighton Buzzard Repair Cafe: booking **Events**, logging **Items**
brought in for repair, tracking volunteer **Skills**, and check-in on the day. Jetstream handles
auth/teams scaffolding; Livewire 2 components handle the interactive UI. See the README for the
domain model and local setup.

## Topic docs

Detail that's too specific/long-lived for this file lives under [agents/](agents/), one file per
topic, linked from here:

- [agents/database.md](agents/database.md) — schema, local DB connection gotchas, seeding
- [agents/design.md](agents/design.md) — Blade/Tailwind/Livewire UI conventions

As you discover something worth remembering for next time (a gotcha, a convention, a "why is it
done this way"), add it to the relevant topic file — create a new one under `agents/` if it doesn't
fit an existing topic (e.g. `agents/deployment.md`, `agents/testing.md`) and link it from this list
and from [AGENTS.md](AGENTS.md). Keep entries dated where the finding is time-sensitive (e.g. "as of
2026-09-18...") so future readers can judge if it's stale.

## Stack specifics

- PHP ^8.0.2, Laravel 9, Jetstream 2 (Livewire stack, not Inertia), Livewire 2 (not 3 — v2 API
  differs from v3, don't use v3-only features like `wire:model.live`)
- Frontend: Blade templates, Tailwind CSS, Alpine.js, Font Awesome, tw-elements, built with Vite
- No PHP/Composer available in some environments — check before assuming you can run `artisan` or
  `phpunit` directly; if unavailable, say so rather than guessing at output

## Conventions

- **Quotes**: PHP code in this repo generally uses double quotes for strings (see
  `app/Models/Item.php`, migrations). Match the surrounding file rather than assuming
  single-quote-per-PSR style.
- **Indentation**: 4 spaces everywhere (`.editorconfig`), 2 spaces for YAML.
- **Formatting**: Prettier is configured (`.prettierrc`) with `@shufo/prettier-plugin-blade` for
  `.blade.php` files. There's no `laravel/pint` config file, so default Pint rules apply if you run
  it — no custom `pint.json`.
- **Livewire components**: PHP class in `app/Http/Livewire/`, paired view in
  `resources/views/livewire/`, same name in kebab-case. Follow the existing components (e.g.
  `CreateItem.php` / `create-item.blade.php`) for the pattern used for public properties, validation,
  and emitted events.
- **Models**: Plain Eloquent, relationships documented with short docblock comments (see
  `app/Models/Item.php`). Soft deletes are used on `Item`. Schema/seeding detail:
  [agents/database.md](agents/database.md).
- **Routes**: Defined in `routes/web.php`, grouped by auth middleware tier — public, then
  `auth:sanctum` + `verified` (logged-in users), then `+ isAdmin` (admins). Add new routes to the
  matching group rather than layering ad-hoc middleware checks in controllers.
- **UI/Blade/Tailwind**: See [agents/design.md](agents/design.md) for component and styling
  conventions.

## Before committing

- If you touched PHP: run `./vendor/bin/pint` (or `sail pint`) and `php artisan test` if PHP is
  available in your environment.
- If you touched JS/CSS/Blade: run `npx prettier --check .` (or `--write` to fix) and
  `npm run build` to make sure Vite compiles cleanly.
- There is no CI configured in this repo yet, so these checks are the only safety net — don't skip
  them.

## Ground rules

- Don't invent deployment steps, hosting details, or environment values that aren't in this repo —
  say what's unknown rather than guessing (see README's Deployment section).
- Don't add new third-party services/API integrations (payment, analytics, external APIs) without
  flagging it explicitly — this is a small volunteer-run project, keep the dependency footprint
  deliberate.
- Prefer extending existing Livewire components/patterns over introducing a new frontend approach
  (e.g. don't reach for a JS framework or Livewire 3 patterns).
