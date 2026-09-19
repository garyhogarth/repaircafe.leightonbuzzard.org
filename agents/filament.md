# Filament (admin panels)

Detail notes on the two Filament panels in this app and how access to them is gated. Linked from
[AGENTS.md](../AGENTS.md).

## Two panels, two resource trees

There are **two separate Filament panels**, each with its own `PanelProvider` in
`app/Providers/Filament/` and its own resource directory — don't confuse them or add a resource to
the wrong tree:

- **`admin`** (`AdminPanelProvider`, path `/admin`, marked `->default()`) — full staff-facing CRUD:
  Events, Items, Venues, Skills, Categories, Users, Roles. Resources live under
  `app/Filament/Resources/`.
- **`dashboard`** (`DashboardPanelProvider`, path `/dashboard`) — the volunteer/fixer-facing panel.
  Resources live under `app/Filament/Dashboard/Resources/` (currently just an `Items` resource) —
  this is the "user dashboard" tracked as still-basic in
  [milestone issue #65](https://github.com/robeastwood/repaircafe.leightonbuzzard.org/issues/65),
  see AGENTS.md's "Known gaps". It also hardcodes navigation links out to the public static pages
  (`route('home')`, `route('more-information')`, etc.) and external community links (Totally
  Leighton Buzzard, Repair Cafe International, iFixit, Right to Repair) via `navigationItems()` —
  add new external links here, not as ad-hoc Blade links elsewhere.
- Each panel cross-links to the other via a user-menu `Action`: `dashboard` shows an "Admin
  Dashboard" link gated on `$user?->can('access-admin-panel')`; `admin` shows a "User Dashboard"
  link with no gate (any admin-panel user can see their own dashboard).

## Resource file structure (Filament 4 style)

Each resource follows the newer Filament 4 split-file convention — don't put form/table/infolist
definitions inline in the Resource class itself:

```
app/Filament/Resources/Events/
├── EventResource.php          # registers the resource, model, navigation
├── Schemas/EventForm.php      # the create/edit form schema
├── Schemas/EventInfolist.php  # the read-only view schema
├── Tables/EventsTable.php     # the list-page table columns/filters/actions
└── Pages/{List,Create,Edit,View}Event.php
```

Follow this same split when adding a new resource rather than reverting to Filament 3's
single-class style.

## Authorization: policies gate both panels

Filament resources don't declare their own permission checks — they defer to the standard Laravel
`Policy` for the model (`app/Policies/`), which Filament auto-discovers and calls
(`viewAny`/`create`/`update`/`delete`/`restore`/`forceDelete`). See
[agents/database.md](database.md) for the permission names themselves.

- Almost every policy method checks a single coarse `manage-{resource}` permission (e.g.
  `EventPolicy` checks `manage-events` for everything except `restore`/`forceDelete`, which check
  `super-admin`) — there's no per-action granularity like `create-events` vs `edit-events`. Follow
  this pattern for new resources/policies rather than inventing finer-grained permissions, to stay
  consistent.
- `super-admin` gates two things consistently across policies: restoring/force-deleting soft-deleted
  records, and *seeing* soft-deleted records at all (via `HideSoftDeletedForNonSuperAdmins`, see
  agents/database.md). If you add soft deletes to a new model, wire both of these the same way.
- `access-admin-panel` is the actual gate on the whole `admin` panel: `User::canAccessPanel()`
  (`app/Models/User.php`) checks it directly for `panel->getId() === 'admin'`, separate from any
  individual resource's `manage-*` permission — a user could have `manage-events` but still be
  refused entry to `/admin` entirely without this permission too. Any authenticated, verified user
  can access the `dashboard` panel (`canAccessPanel()` returns `true` unconditionally for it).
  `DashboardPanelProvider`'s "Admin Dashboard" menu link re-checks the same permission just to decide
  whether to show the link, not as a separate gate.
