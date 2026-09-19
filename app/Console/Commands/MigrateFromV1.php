<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off data migration from the v1 (Laravel 9) database into this v2 app.
 *
 * v1 and v2 share almost the same table shapes (see agents/database.md), so most tables are a
 * straight row copy. Two tables need transformation rather than a copy:
 * - `users`: v1 stores authorization as boolean columns (is_admin/volunteer/fixer) on the user
 *   row itself; v2 uses Spatie roles, so those flags are converted into role assignments.
 * - `event_item`: v2 added a unique (event_id, item_id) constraint that v1 never enforced, so
 *   duplicate rows are deduplicated before insert.
 *
 * Sessions and password-reset tokens are intentionally not migrated: v2 has a different APP_KEY,
 * so old session payloads and cookies can't be decrypted there anyway — everyone is expected to
 * log back in after cutover.
 */
class MigrateFromV1 extends Command
{
    protected $signature = 'migrate:from-v1
        {--dry-run : Report row counts without writing anything}
        {--fresh : Wipe destination domain tables before importing}';

    protected $description = 'Copy data from the v1 database (connection "legacy") into this v2 database';

    private const LEGACY = 'legacy';

    public function handle(): int
    {
        if (! $this->confirmLegacyConnection()) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->reportCounts();

            return self::SUCCESS;
        }

        if (! $this->option('fresh') && DB::table('users')->exists()) {
            $this->error('Destination `users` table already has data. Re-run with --fresh to wipe domain tables first, or start from a clean database.');

            return self::FAILURE;
        }

        DB::transaction(function () {
            if ($this->option('fresh')) {
                $this->wipeDomainTables();
            }

            $this->copyVenues();
            $this->copyCategories();
            $this->copySkills();
            $this->copyEvents();
            $this->copyUsers();
            $this->copyItems();
            $this->copyEventItem();
            $this->copyNotes();
            $this->copyEventUser();
            $this->copySkillUser();
        });

        $this->resetAutoIncrements();

        $this->info('v1 -> v2 data migration complete.');

        return self::SUCCESS;
    }

    private function confirmLegacyConnection(): bool
    {
        try {
            DB::connection(self::LEGACY)->getPdo();
        } catch (\Throwable $e) {
            $this->error('Could not connect to the "legacy" database connection: '.$e->getMessage());
            $this->line('Set LEGACY_DB_* in .env — see .env.example.');

            return false;
        }

        return true;
    }

    private function reportCounts(): void
    {
        $tables = ['venues', 'categories', 'skills', 'events', 'users', 'items', 'event_item', 'notes', 'event_user', 'skill_user'];

        foreach ($tables as $table) {
            $count = DB::connection(self::LEGACY)->table($table)->count();
            $this->line(sprintf('%-12s %d rows', $table, $count));
        }
    }

    private function wipeDomainTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['skill_user', 'event_user', 'notes', 'event_item', 'items', 'users', 'events', 'skills', 'categories', 'venues'] as $table) {
            DB::table($table)->delete();
        }

        DB::table('model_has_roles')->delete();

        Schema::enableForeignKeyConstraints();
    }

    private function copyVenues(): void
    {
        $this->copyTable('venues', fn ($row) => (array) $row);
    }

    private function copyCategories(): void
    {
        $this->copyTable('categories', fn ($row) => (array) $row);
    }

    private function copySkills(): void
    {
        // v2's `description` has always been nullable; v1's became nullable in a later
        // migration, so old rows may already contain the same NULL/empty values either way.
        $this->copyTable('skills', fn ($row) => (array) $row);
    }

    private function copyEvents(): void
    {
        $this->copyTable('events', fn ($row) => (array) $row);
    }

    /**
     * v1's `is_admin` / `volunteer` / `fixer` booleans become Spatie role assignments in v2.
     * Jetstream-only columns (`current_team_id`, `profile_photo_path`) have no v2 equivalent
     * and are dropped; everything else (including the bcrypt password hash and 2FA secrets,
     * which use the same algorithm/config in both apps) copies across unchanged.
     */
    private function copyUsers(): void
    {
        $rows = DB::connection(self::LEGACY)->table('users')->orderBy('id')->get();

        $roleAssignments = [];

        foreach ($rows as $row) {
            $attrs = (array) $row;

            $isAdmin = (bool) ($attrs['is_admin'] ?? false);
            $isVolunteer = (bool) ($attrs['volunteer'] ?? false);
            $isFixer = (bool) ($attrs['fixer'] ?? false);

            unset(
                $attrs['is_admin'],
                $attrs['volunteer'],
                $attrs['fixer'],
                $attrs['current_team_id'],
                $attrs['profile_photo_path'],
            );

            DB::table('users')->insert($attrs);

            if ($isAdmin) {
                $roleAssignments[] = ['user_id' => $row->id, 'role' => 'admin'];
            }
            if ($isVolunteer) {
                $roleAssignments[] = ['user_id' => $row->id, 'role' => 'volunteer'];
            }
            if ($isFixer) {
                $roleAssignments[] = ['user_id' => $row->id, 'role' => 'fixer'];
            }
        }

        $this->assignRoles($roleAssignments);

        $this->info(sprintf('users: copied %d rows, assigned %d roles', $rows->count(), count($roleAssignments)));
    }

    private function assignRoles(array $roleAssignments): void
    {
        if ($roleAssignments === []) {
            return;
        }

        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach (['admin', 'volunteer', 'fixer'] as $role) {
            if (! isset($roleIds[$role])) {
                $this->warn("Role \"{$role}\" not found — run PermissionSeeder/RoleSeeder before this command.");
            }
        }

        $pivotRows = collect($roleAssignments)
            ->filter(fn ($a) => isset($roleIds[$a['role']]))
            ->map(fn ($a) => [
                'role_id' => $roleIds[$a['role']],
                'model_type' => \App\Models\User::class,
                'model_id' => $a['user_id'],
            ])
            ->all();

        foreach (array_chunk($pivotRows, 500) as $chunk) {
            DB::table('model_has_roles')->insertOrIgnore($chunk);
        }
    }

    private function copyItems(): void
    {
        $this->copyTable('items', fn ($row) => (array) $row);
    }

    /**
     * v2 added a unique (event_id, item_id) constraint that v1 never had, and `event_item` has
     * no id/timestamp column to order by, so "most recent" can't be determined. Among
     * duplicates, keep whichever row looks most complete (has a repairer and/or a check-in
     * time) and drop the rest.
     */
    private function copyEventItem(): void
    {
        $rows = DB::connection(self::LEGACY)->table('event_item')->get();

        $deduped = $rows
            ->groupBy(fn ($row) => $row->event_id.':'.$row->item_id)
            ->map(fn ($group) => $group->sortByDesc(fn ($row) => ($row->repairer_id !== null ? 1 : 0) + ($row->checkedin !== null ? 1 : 0))->first());

        $skipped = $rows->count() - $deduped->count();
        if ($skipped > 0) {
            $this->warn("event_item: dropped {$skipped} duplicate (event_id, item_id) row(s) to satisfy v2's unique constraint.");
        }

        foreach ($deduped->chunk(500) as $chunk) {
            DB::table('event_item')->insert($chunk->map(fn ($row) => (array) $row)->all());
        }

        $this->info("event_item: copied {$deduped->count()} rows.");
    }

    private function copyNotes(): void
    {
        $this->copyTable('notes', fn ($row) => (array) $row);
    }

    private function copyEventUser(): void
    {
        $this->copyPivotTable('event_user');
    }

    private function copySkillUser(): void
    {
        $this->copyPivotTable('skill_user');
    }

    private function copyTable(string $table, callable $transform): void
    {
        $rows = DB::connection(self::LEGACY)->table($table)->orderBy('id')->get();

        foreach ($rows->chunk(500) as $chunk) {
            DB::table($table)->insert($chunk->map($transform)->all());
        }

        $this->info("{$table}: copied {$rows->count()} rows.");
    }

    /**
     * For plain pivot tables with no id column of their own (event_user, skill_user).
     */
    private function copyPivotTable(string $table): void
    {
        $rows = DB::connection(self::LEGACY)->table($table)->get();

        foreach ($rows->chunk(500) as $chunk) {
            DB::table($table)->insert($chunk->map(fn ($row) => (array) $row)->all());
        }

        $this->info("{$table}: copied {$rows->count()} rows.");
    }

    /**
     * Rows above were inserted with explicit ids copied from v1, so the destination's
     * auto-increment sequence needs bumping past the highest id or the next native insert
     * (e.g. a new user signing up) will collide.
     *
     * SQLite needs no action: `$table->id()` is a plain `INTEGER PRIMARY KEY` (a rowid alias,
     * without the `AUTOINCREMENT` keyword), so its next id already follows `max(rowid) + 1`
     * automatically — there's no `sqlite_sequence` row to update.
     */
    private function resetAutoIncrements(): void
    {
        $driver = DB::connection()->getDriverName();
        $tables = ['venues', 'categories', 'skills', 'events', 'users', 'items', 'notes'];

        foreach ($tables as $table) {
            $maxId = (int) DB::table($table)->max('id');
            if ($maxId === 0) {
                continue;
            }

            match ($driver) {
                'sqlite' => null,
                'mysql', 'mariadb' => DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = ".($maxId + 1)),
                'pgsql' => DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$maxId})"),
                default => $this->warn("Don't know how to reset auto-increment for driver \"{$driver}\" — check {$table}.id manually."),
            };
        }
    }
}
