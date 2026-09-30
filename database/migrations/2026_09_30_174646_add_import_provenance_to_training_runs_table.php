<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two columns recording that a run arrived by file rather than by keystroke (ADR-0017, PRD US-12's
 * neighbouring concern: a Trainer looking at twenty-four turns two years later needs to know whether the
 * gaps in them are their memory or the format's).
 *
 * Both are nullable with no default, because the overwhelming majority of runs are typed and must not be
 * made to look imported. `imported_at` deliberately does **not** reuse `created_at`: an import writes the
 * row today, so `created_at` is when the record entered the database and `imported_at` is what the Trainer
 * asserted about the run's own past. Collapsing them would make every imported run claim to have been
 * created at import time, which is true of the row and false of the history.
 *
 * Not confused with the two provenance pairs already in the schema, which is why the names were grepped
 * before being chosen: `is_manual` (FR-B-4) marks a row the fetch engine must never overwrite, and
 * `source_url`/`fetched_at` mark a fetched reference row's origin. An imported run is neither a
 * hand-correction nor a fetch, so it gets its own pair.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->timestamp('imported_at')->nullable()->after('notes');
            $table->string('import_source')->nullable()->after('imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['import_source', 'imported_at']);
        });
    }
};
