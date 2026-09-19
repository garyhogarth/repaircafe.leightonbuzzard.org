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

## Formatting

- `.blade.php` files are formatted with Prettier + `@shufo/prettier-plugin-blade`
  (`.prettierrc`), 4-space indentation. Run `npx prettier --write` on Blade files you touch.
