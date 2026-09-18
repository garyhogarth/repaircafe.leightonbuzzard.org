# AGENTS.md

This is a Laravel 9 app for the Leighton Buzzard Repair Cafe (event booking, item repair tracking,
volunteer management). See [README.md](README.md) for setup and the domain model.

Conventions, stack details, and ground rules for AI agents working in this repo are kept in
[CLAUDE.md](CLAUDE.md) — treat it as the source of truth regardless of which agent/tool you are.
Keep this file and CLAUDE.md in sync; if you update one, check whether the other needs the same
change.

Deeper topic-specific detail (database, design/UI, and more as they come up) lives under
[agents/](agents/) — see CLAUDE.md's "Topic docs" section for the index and the convention for
adding new ones.

## Quick reference

- Install: `composer install && npm install` (or `./vendor/bin/sail up -d` for Docker)
- Build assets: `npm run build` (dev: `npm run dev`)
- Tests: `php artisan test`
- Lint/format: `./vendor/bin/pint` (PHP), `npx prettier --write .` (JS/CSS/Blade)
- No CI is configured — run the above locally before considering a change done.
