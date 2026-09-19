# Dependencies / upgrade progress

Detail notes for AI agents working on framework/package upgrades. Linked from
[CLAUDE.md](../CLAUDE.md) / [AGENTS.md](../AGENTS.md).

## Laravel major-version upgrade

This repo started on Laravel 9 (2026-09-18) and is being moved forward one major version at a time,
per Laravel's own recommendation not to skip majors. Livewire 2→3 (a separate, larger breaking
change — see [agents/design.md](design.md)) is being deferred until after the Laravel version chain
is current.

- [x] **9 → 10** (2026-09-18): `composer.json` bumped (`php ^8.1`, `laravel/framework ^10.48`,
  `laravel/sanctum ^3.2`, `spatie/laravel-ignition ^2.0`). No app code changes were needed — none of
  Laravel 10's breaking-change patterns (`$dates` property, `DB::raw` string casts,
  `MocksApplicationServices`, `Redirect::home`, `dispatchNow`, custom form-request `after()`) appear
  anywhere in this codebase, and the installed Jetstream 2.16.2 already declared support for
  `illuminate/support ^9.21|^10.0`. Full test suite (28 passing, 4 pre-existing skips) and a manual
  homepage/login smoke test both passed after the bump.
- [ ] **10 → 11**: not started.
- [ ] **11 → 12**: not started.
- [ ] **12 → 13**: not started.

## Accepted security-advisory exceptions

`composer.json`'s `config.audit.ignore` currently ignores 3 Packagist security advisories, all on
`laravel/framework`, all because **no fix exists anywhere in the 10.x line** — they were only
resolved by architectural changes in later majors:

| Advisory | Issue | Fixed in |
|---|---|---|
| `PKSA-m5cs-t1y6-qpcs` | Temporary signed URL path confusion | 12.61.1 / 13.12.0 |
| `PKSA-3r5d-mb8f-1qw9` | CRLF injection in default email rule (variant) | 12.60.0 / 13.10.0 |
| `PKSA-mdq4-51ck-6kdq` | CRLF injection in default email rule | 11.0.0 |

Discovered 2026-09-18 during the 9→10 step: `composer update` refused to install *any* 10.x release
(including the newest, v10.50.3) because of these. `laravel/framework` was constrained to `^10.48`
specifically to pick up the two *other* blocking advisories that do have 10.x fixes
(file-validation bypass, fixed 10.48.29; env-manipulation via query string, fixed 10.48.23).

**These three ignores should be removed** (delete the `config.audit.ignore` entries, or the whole
block if empty) as part of whichever future upgrade step lands on Laravel 11 or later — check
`composer audit` after that step to confirm they're actually resolved before deleting the block.

Everything else `composer audit` flagged during the 9→10 step (Guzzle, guzzlehttp/psr7, Livewire —
including a *high-severity* RCE via file uploads, PHPUnit, psysh, symfony/yaml) was **not** a
Laravel-version issue — those packages were just pinned to old versions that predated the existing
`composer.json` constraints. A plain `composer update` (no package scoping) picked up already-fixed
patch releases within the existing constraints and cleared all of them. If `composer audit` ever
shows a long list like this again, try a full unscoped update before assuming a version bump is
needed.
