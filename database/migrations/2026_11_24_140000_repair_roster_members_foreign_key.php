<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair the `roster_members.umamusume_id` foreign key on databases that applied the
 * original `2026_11_24_120000_create_roster_members_table` definition.
 *
 * That definition let Laravel pluralise the singular `umamusume` table into
 * `umamusumes`. SQLite stores the referenced table name inside each table's
 * definition, so correcting the create migration alone does not help a database
 * that already ran it: `migrate:status` reports it Ran and the definition stays
 * stale. A dangling FK is not inert - SQLite resolves every FK of a child table
 * when a parent `training_runs` row is deleted, so every DELETE failed with
 * `no such table: main.umamusumes` even on runs with no roster rows at all.
 *
 * The rebuild runs only when the dangling target is present, so a fresh database
 * created from the corrected migration keeps its table untouched. Rows and the
 * ordering index are carried across verbatim; no other table is touched.
 *
 * `down()` is deliberately forward-only: rolling a data-integrity repair back
 * would have to reinstate the broken `umamusumes` reference, which is never a
 * valid database state. `migrate:rollback` still succeeds and leaves the correct
 * schema in place, so the repair converges with the corrected create migration.
 */
return new class extends Migration
{
    private const TABLE = 'roster_members';

    private const REPAIRED_TABLE = 'roster_members_repaired';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $foreignKeys = DB::select("PRAGMA foreign_key_list('".self::TABLE."')");

        $isBroken = collect($foreignKeys)->contains(
            fn (object $foreignKey): bool => $foreignKey->table === 'umamusumes',
        );

        if (! $isBroken) {
            return;
        }

        Schema::create(self::REPAIRED_TABLE, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained()->cascadeOnDelete();
            // `umamusume` is singular; the target must be named explicitly.
            $table->foreignId('umamusume_id')->nullable()->constrained('umamusume')->nullOnDelete();
            $table->string('name');
            $table->char('grade', 1)->nullable();
            $table->unsignedTinyInteger('speed')->nullable();
            $table->unsignedTinyInteger('stamina')->nullable();
            $table->unsignedTinyInteger('power')->nullable();
            $table->unsignedTinyInteger('guts')->nullable();
            $table->unsignedTinyInteger('wit')->nullable();
            $table->string('distance_assignment')->nullable();
            $table->boolean('is_ace')->default(false);
            $table->boolean('is_trainee')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->index(['training_run_id', 'position']);
        });

        $columns = [
            'id', 'training_run_id', 'umamusume_id', 'name', 'grade', 'speed', 'stamina',
            'power', 'guts', 'wit', 'distance_assignment', 'is_ace', 'is_trainee',
            'position', 'created_at', 'updated_at',
        ];
        $quoted = implode(', ', array_map(
            fn (string $column): string => '"'.$column.'"',
            $columns,
        ));

        DB::transaction(function () use ($quoted): void {
            DB::statement(
                'INSERT INTO "'.self::REPAIRED_TABLE.'" ('.$quoted.') '
                .'SELECT '.$quoted.' FROM "'.self::TABLE.'"',
            );

            Schema::drop(self::TABLE);
            Schema::rename(self::REPAIRED_TABLE, self::TABLE);
        });
    }

    public function down(): void
    {
        // Forward-only by design. See the class docblock.
    }
};
