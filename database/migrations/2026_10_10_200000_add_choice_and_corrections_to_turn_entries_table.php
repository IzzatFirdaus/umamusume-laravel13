<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FD-4's schema change (rulings D-1a, D-1b, D-1c, D-1d).
 *
 * **The index swap is the point of the migration, not an incidental.** `turn_entries` has carried
 * `unique(['training_run_id', 'turn'])` since `2026_09_26_162818_create_turn_entries_table.php:24`, which
 * is what makes a correction-by-append impossible: two rows at one turn cannot coexist. D-1b replaces it
 * with a **partial** unique index that constrains only rows that are not themselves corrections.
 *
 * **Why the predicate is `corrected_from IS NULL` and not `superseded_by IS NULL`.** A partial unique index
 * is checked per statement, and a correction cannot be superseded-before-inserted: its own id does not exist
 * until the insert succeeds, so the original it replaces is still `superseded_by IS NULL` at that moment. An
 * index over the current row therefore rejects every append. Measured on a scratch database: inserting the
 * pair under `WHERE superseded_by IS NULL` raised `UNIQUE constraint failed: turn_entries.training_run_id,
 * turn_entries.turn`. Predicating on `corrected_from` keeps the guarantee the database has always held, one
 * original per turn, and exempts the row that corrects it. That "at most one row per turn is still current"
 * is then the write path's job, not the schema's: a schema that refused a second correction would also
 * refuse a legitimate correction of a correction, which no ruling has asked for.
 *
 * Laravel's `Schema` builder has no partial index, so this is raw SQL against SQLite. The index name is
 * written by hand, and `down()` reverses exactly that: drop the partial index, drop the lookup index, drop
 * the four columns, recreate the plain unique.
 *
 * **`down()` refuses rather than deleting history.** If any turn holds more than one row, the plain
 * `unique(['training_run_id', 'turn'])` cannot be recreated, and `down()` raises before it has dropped
 * anything. That is deliberate: the alternative is a `down()` that silently discards correction rows to
 * make itself succeed, which is the one thing a rollback must not do to an append-only history. Measured:
 * before the guard, a rollback on a scratch database holding one correction pair dropped the partial index
 * and then failed, leaving the four columns in place with no unique constraint at all.
 *
 * **`superseded_by` marks the row a reader should stop reading**, and `corrected_from` marks the row that
 * replaced it. Both directions are stored because the pair is read from either end:
 * `NextDecision::recordedAction()` takes the current row of a turn (`superseded_by IS NULL`) and the
 * recorded-action card prints the link back to the row it corrects.
 */
return new class extends Migration
{
    private const PARTIAL_INDEX = 'turn_entries_run_turn_original_unique';

    private const LOOKUP_INDEX = 'turn_entries_run_turn_lookup';

    public function up(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            // The action the Trainer took, as a TurnChoice value. Nullable because every row written
            // before this migration has no action to name, and back-filling one would be inventing a
            // record the tool never held (`AGENTS.md` §5: no fact stored without provenance).
            $table->string('choice', 32)->nullable()->after('condition');

            // The advisor's full NextDecision payload as it stood at the moment of the action, written
            // once by the server and never updated (D-1a). A recomputed value is not a snapshot.
            $table->json('decision_snapshot')->nullable()->after('choice');

            // The cross-link pair (D-1c). Both directions are stored so neither row has to scan the
            // other to explain itself in the Timeline.
            $table->unsignedBigInteger('corrected_from')->nullable()->after('decision_snapshot');
            $table->unsignedBigInteger('superseded_by')->nullable()->after('corrected_from');

            $table->foreign('corrected_from')
                ->references('id')->on('turn_entries')->nullOnDelete();
            $table->foreign('superseded_by')
                ->references('id')->on('turn_entries')->nullOnDelete();
        });

        // Laravel 13's SQLiteBuilder has no `dropIndex`, and the name the framework generated for
        // `unique(['training_run_id', 'turn'])` is a convention rather than a contract. So the index is
        // resolved from the catalog at runtime and dropped by raw SQL, which also makes the migration
        // correct on a database whose index was named by an older framework version. The `NOT LIKE '%WHERE%'`
        // keeps the lookup from matching a partial index, including this migration's own if it is re-run.
        $original = DB::selectOne(
            "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'turn_entries' "
            ."AND sql LIKE '%UNIQUE%training_run_id%turn%' AND sql NOT LIKE '%WHERE%' LIMIT 1",
        );

        if ($original === null) {
            throw new RuntimeException(
                'FD-4: no plain unique index over (training_run_id, turn) was found on turn_entries, '
                .'so the swap to the partial index cannot be proven. Reporting it here beats letting '
                .'corrections fail later with an unexplained UNIQUE constraint error.'
            );
        }

        DB::statement('DROP INDEX "'.(string) $original->name.'"');

        DB::statement('CREATE UNIQUE INDEX '.self::PARTIAL_INDEX
            .' ON turn_entries (training_run_id, turn) WHERE corrected_from IS NULL');

        // The partial index does not serve an ordinary lookup on (run, turn) for every row, so the
        // queries that address a turn keep a plain index to work from.
        DB::statement('CREATE INDEX '.self::LOOKUP_INDEX.' ON turn_entries (training_run_id, turn)');
    }

    public function down(): void
    {
        // Refuse before touching anything. SQLite has no transactional DDL here, so a `down()` that learns
        // it cannot finish halfway through leaves the schema in a mixed state: measured on a scratch
        // database holding one correction pair, the partial index was already dropped and the four columns
        // still present when the recreate raised. The check is "any turn holds more than one row", which is
        // exactly the collision the plain unique would raise, stated before the work rather than after it.
        $clustered = DB::selectOne(
            'SELECT count(*) as aggregate FROM ('
            .'SELECT training_run_id, turn FROM turn_entries'
            .' GROUP BY training_run_id, turn HAVING count(*) > 1)'
        );

        if ((int) $clustered->aggregate > 0) {
            throw new RuntimeException(
                'FD-4: this database holds corrections, so turn_entries has more than one row for at least '
                .'one turn and the plain unique(['
                .'training_run_id, turn]) this migration replaces cannot be recreated. Rolling back would '
                .'mean deleting correction rows. History is not an agent or a migration to discard: restore '
                .'a pre-correction database, or leave this migration applied.'
            );
        }

        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropForeign(['corrected_from']);
            $table->dropForeign(['superseded_by']);
        });

        DB::statement('DROP INDEX IF EXISTS '.self::PARTIAL_INDEX);
        DB::statement('DROP INDEX IF EXISTS '.self::LOOKUP_INDEX);

        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropColumn(['choice', 'decision_snapshot', 'corrected_from', 'superseded_by']);
        });

        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->unique(['training_run_id', 'turn']);
        });
    }
};
