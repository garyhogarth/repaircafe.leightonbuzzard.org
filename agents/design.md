# Design / Frontend

Detail notes for AI agents touching UI (Blade, Livewire/Volt views, Flux, Tailwind). Linked from
[AGENTS.md](../AGENTS.md).

## Stack

- **Livewire 3** (not 2 — this is the opposite of v1; don't reach for v2-era patterns like
  `emit`/`emitTo`, use `dispatch`) plus **Livewire Volt** for single-file components.
- **Flux UI** (`livewire/flux`, free tier — see AGENTS.md) is the component library: use its
  components (`<flux:input>`, `<flux:button>`, etc.) rather than hand-rolling form controls in raw
  Blade/Tailwind. Do not reach for Pro-only components (`livewire/flux-pro`) without flagging the
  license requirement first.
- **Tailwind CSS 4** — configured entirely in CSS via `@theme` in `resources/css/app.css`, no
  `tailwind.config.js` file exists on this branch. Custom color tokens (`--color-accent`,
  `--color-accent-content`, etc.) and the dark-mode variant (`@custom-variant dark`) are defined
  there — extend that file for new design tokens rather than adding a Tailwind config file.
- `@source` directives in `app.css` tell Tailwind where to scan for classes, including
  `vendor/livewire/flux/stubs` and `vendor/livewire/flux-pro/stubs` (the latter harmless to leave in
  even without the Pro package installed — it just won't match anything).

## Auth & settings UI

- `app/Livewire/Auth/*` (Login, Register, ResetPassword, VerifyEmail, ForgotPassword) and
  `app/Livewire/Settings/*` (Profile, Password, Appearance, TwoFactor) are full-class Livewire
  components with paired Blade views in `resources/views/livewire/auth/` and
  `resources/views/livewire/settings/` (not Volt) — follow this pairing (class + separate view) for
  anything similarly stateful, rather than inlining a Volt single-file component, to stay consistent
  with the existing auth/settings code.
- `app/Livewire/ContactForm.php` is the one non-auth/settings full-class Livewire component so far —
  the pattern for any future public-facing interactive form.

## Layout

- `resources/views/partials/head.blade.php` and `resources/views/partials/settings-heading.blade.php`
  are shared partials — check here before duplicating `<head>` boilerplate or settings-page headers.
- Static pages (`welcome`, `contact`, `policies`, `more-information`, `repair-disclaimer`) are plain
  Blade views returned directly from closures in `routes/web.php`, not Livewire/Volt components —
  keep genuinely static pages this way rather than promoting them to Livewire for no reason.

## Build / Vite

- `vite.config.js` declares both `resources/css/app.css` and `resources/js/app.js` as entries, with
  `refresh: true` on the Laravel plugin (auto full-page reload on Blade/PHP changes in dev).
- **`public/build/` and `public/hot` ARE gitignored on this branch** (unlike v1, where
  `public/build` is deliberately committed) — this branch expects a build step in deployment/CI, not
  committed compiled assets. Don't commit `public/build/*`.
- If you see a stale `public/hot` file and the site 502s or serves no CSS, it means a `npm run dev`
  process (possibly from a different tool, e.g. Sail on another branch/checkout) wrote that marker
  and isn't actually running anymore — delete it to fall back to `public/build`, or actually start
  `npm run dev`.

## Formatting

No Prettier is configured on this branch (no `.prettierrc`, no `prettier` in `package.json`) — see
AGENTS.md's Conventions section. Don't assume Blade gets auto-formatted; match surrounding style by
hand.
