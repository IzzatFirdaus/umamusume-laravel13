<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converges `transfer_requests` onto one shape.
 *
 * The table exists in two, measured 2026-11-15. Every database built from the migration files
 * carries the shape `2026_11_14_023609` declares: `on delete set null on update cascade`, with the
 * full unique `one_per_veteran_per_status (veteran_id, status)`. The dev database was built from an
 * earlier draft of that same migration and carries `on delete cascade` (NOT NULL `veteran_id`) with
 * the column-only unique `one_offered_per_veteran (veteran_id)` — a name no file carries. Two 500s
 * came out of the split, each visible on one shape only:
 *
 * - On the dev shape, completing a transfer cascade-deleted the audit row and
 *   `CompleteTransferRequest` fataled re-reading it (`fresh()` on a deleted row). The suite stays
 *   green through this because the in-memory test database builds only the fresh shape.
 * - On either shape, a Veteran's second `withdrawn` row violated the full unique
 *   (offer -> withdraw -> offer -> withdraw).
 *
 * This is a separate corrective file rather than an edit to `2026_11_14_023609`: the dev database
 * has already run that migration (recorded `Ran`), so editing it would rewrite a revision this
 * database has consumed. Every step below is guarded, so it is a no-op on a database already in
 * the target shape and applies only the deltas on the other.
 *
 * The partial index is raw SQL because Laravel's fluent builder compiles `->where()` away on
 * SQLite (`SQLiteGrammar::compileUnique()` emits no WHERE clause, measured), and a column-only
 * unique would forbid a Veteran's offered and withdrawn rows from coexisting at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Retire whichever full/column-only unique this database carries.
        if (Schema::hasIndex('transfer_requests', 'one_per_veteran_per_status')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->dropIndex('one_per_veteran_per_status');
            });
        }

        if (Schema::hasIndex('transfer_requests', 'one_offered_per_veteran')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->dropIndex('one_offered_per_veteran');
            });
        }

        // 2. The audit label `CompleteTransferRequest` stamps before it deletes the Veteran:
        //    `nullOnDelete` nulls the link, so without this the surviving audit row can no longer
        //    say who was transferred.
        if (! Schema::hasColumn('transfer_requests', 'veteran_label')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->string('veteran_label')->nullable()->after('reward');
            });
        }

        // 3. The dev shape's `veteran_id` is NOT NULL with a cascade delete. Both properties
        //    change: the cascade removed the audit row this table exists to keep, and NOT NULL
        //    cannot hold the null a deleted Veteran's row now carries.
        $veteranColumn = collect(Schema::getColumns('transfer_requests'))->firstWhere('name', 'veteran_id');

        if (($veteranColumn['nullable'] ?? false) === false) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->dropForeign(['veteran_id']);
                $table->unsignedBigInteger('veteran_id')->nullable()->change();
                $table->foreign('veteran_id')->references('id')->on('veterans')->nullOnDelete()->cascadeOnUpdate();
            });
        }

        // 4. One offered request per Veteran, and only an offered one: a Veteran's withdrawn and
        //    completed rows accumulate freely, because they are the audit trail.
        if (! Schema::hasIndex('transfer_requests', 'one_offered_per_veteran')) {
            DB::statement("create unique index one_offered_per_veteran on transfer_requests (veteran_id) where status = 'offered'");
        }
    }

    /**
     * Removes this migration's additions and restores the full unique `2026_11_14_023609` declares.
     *
     * The foreign-key shape is deliberately NOT reverted: `nullOnDelete` is the contract the
     * original migration itself declares, it is the shape both databases converge on, and a
     * rollback that reinstated the cascade delete would resurrect the data loss this correction
     * exists to remove. The full unique's restoration refuses by itself (measured: SQLite rejects
     * the index) when the data holds a Veteran with two `withdrawn` rows — the correct loud
     * refusal, since silently deleting audit rows to satisfy it would not be a rollback.
     */
    public function down(): void
    {
        if (Schema::hasIndex('transfer_requests', 'one_offered_per_veteran')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->dropIndex('one_offered_per_veteran');
            });
        }

        if (Schema::hasColumn('transfer_requests', 'veteran_label')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->dropColumn('veteran_label');
            });
        }

        if (! Schema::hasIndex('transfer_requests', 'one_per_veteran_per_status')) {
            Schema::table('transfer_requests', function (Blueprint $table): void {
                $table->unique(['veteran_id', 'status'], 'one_per_veteran_per_status');
            });
        }
    }
};
