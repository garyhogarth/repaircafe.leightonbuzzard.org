# Design / Frontend

Detail notes for AI agents touching UI (Blade, Livewire views, Tailwind). Linked from
[CLAUDE.md](../CLAUDE.md) / [AGENTS.md](../AGENTS.md).

## Stack

- Tailwind CSS (utility classes directly in Blade, no separate component CSS files), configured in
  `tailwind.config.js`. Font is Nunito, loaded from Google/Bunny fonts in
  `resources/views/layouts/app.blade.php`, extending `tailwindcss/defaultTheme` — don't hardcode a
  different font-family.
- Icons: Font Awesome free (`@fortawesome/fontawesome-free`), used as `<i class="fas fa-...">` /
  `far fa-...` inline, not an icon component.
- `tw-elements` is included as a Tailwind plugin/JS lib for richer components (modals, dropdowns
  etc.) — check there before hand-rolling interactive UI.
- Alpine.js for lightweight interactivity that doesn't need a full Livewire round-trip.
- Livewire 2 (not 3) drives the stateful interactive parts — forms, item/event lists, checkin flow.

## Component conventions

- Blade components live in `resources/views/components/`, referenced as `<x-name>`. They lean on
  `$attributes->merge([...])` so callers can add/override classes — follow this pattern for new
  components rather than hardcoding a fixed class list with no merge point (see
  `abutton.blade.php`).
- Status/state pills (e.g. `pill-status.blade.php` for item status, `pill-powered.blade.php`) use a
  `@switch` over a fixed set of string values, each mapped to a Tailwind color pair
  (`bg-*-200 text-*-800`) and a Font Awesome icon. If you add a new status value, add its case here
  — and keep it consistent with the color/icon mapping already defined in PHP
  (`Item::statusOptions()` in `app/Models/Item.php`, which is the authoritative list of valid
  values and their intended colors/icons — the two should never drift).
- Color convention for status pills: gray = neutral/initial, blue = in progress, green = success,
  yellow = blocked/waiting, red = failure/terminal. Reuse this palette for any new status-like UI
  rather than picking arbitrary colors.

## Layout

- `resources/views/layouts/app.blade.php` is the authenticated app shell (nav, page header slot,
  Jetstream banner); `layouts/guest.blade.php` is for auth pages. New authenticated pages should
  extend/use the `app` layout via `<x-app-layout>`, not duplicate the `<html>` scaffold.
- Livewire component views live in `resources/views/livewire/`, one file per component in
  `app/Http/Livewire/`, matched by kebab-case name.

## Build / Vite

- `resources/css/app.css` and `resources/js/app.js` are both declared as separate entries in
  `vite.config.js`'s `input` array, and both are passed to `@vite([...])` in
  `layouts/app.blade.php` and `layouts/guest.blade.php`. Keep it this way — do **not** move the CSS
  import back inside `app.js` (`import "../css/app.css"`). A CSS file only imported from JS is
  served by Vite's dev server as a JS module that injects a `<style>` tag at runtime, instead of a
  real `<link rel="stylesheet">`; since this app does full (non-SPA) page reloads rather than
  client-side routing, that ordering causes a visible flash of unstyled content on every navigation
  in local dev, most noticeable on large unsized elements like the SVG logos (which is why they also
  carry explicit fallback `width`/`height` attributes — see `authentication-card-logo.blade.php` and
  the published `application-mark.blade.php`). Discovered/fixed 2026-09-18.
- `public/build/` is deliberately **not** gitignored (`.gitignore` has `!/public/build`) — this repo
  has no CI build step, so compiled assets are committed directly and that's what the live site
  serves. Run `npm run build` (or `sail npm run build`) and commit the resulting
  `public/build/assets/*` + `public/build/manifest.json` whenever you change anything under
  `resources/js` or `resources/css`, or your changes won't reach production.

## Formatting

- `.blade.php` files are formatted with Prettier + `@shufo/prettier-plugin-blade`
  (`.prettierrc`), 4-space indentation. Run `npx prettier --write` on Blade files you touch.
