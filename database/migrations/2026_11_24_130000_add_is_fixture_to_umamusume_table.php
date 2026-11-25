<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BO-003 / EX-02: a trainee row that a test factory fabricated is not a catalogue entry.
 *
 * **Why a column and not an inference.** Nothing in the existing schema tells a fabricated row from a
 * published one. `external_ref` is the source's own id, but a `null` there means "no fetch has stamped
 * this row yet", which is also the normal state of a trainee a Trainer added by hand — classifying on it
 * would delete legitimate records from the catalogue. `is_manual` marks the Trainer's own rows, which are
 * the opposite of what this excludes. The name, the id, the timestamps, the rarity and `release_status`
 * are all values a real row can hold, so none of them can separate the two populations (BO-003).
 *
 * A `boolean` that the factory sets and nothing else does is the one honest signal: it states
 * provenance ("this row was fabricated for a test") rather than guessing it from a value. `false` is the
 * default, so every existing row — seeded, fetched, imported, hand-entered — keeps its current catalogue
 * visibility without a backfill, and only rows the factory writes are marked.
 *
 * Not `defaultTo`-only: the factory writes `true` explicitly in `definition()`, so a state can opt a
 * fixture back in where a feature test needs a catalogue-eligible character. That is the escape hatch
 * `UmamusumeFactory::catalogue()` provides, and it is why this is a column rather than a global scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            $table->boolean('is_fixture')->default(false)->index();
        });
    }

    public function down(): void
    {
        // The index goes in its own call before the column, the order
        // `2026_09_29_120000_add_external_ref_to_umamusume_table.php:down()` records: SQLite
        // refuses to drop a column its index still names.
        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropIndex(['is_fixture']);
        });

        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropColumn('is_fixture');
        });
    }
};
