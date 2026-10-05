<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Veteran library (PRD FR-G, ADR-0020 §3, under the recording allowance of ADR-0010).
 *
 * A Veteran is a completed run kept in the Trainer's library. The run's own recorded facts — its trainee,
 * final stats, skills, sparks and race record — are read back through `training_run_id`, so this table
 * holds only the pointer plus the two things only the Trainer supplies: a tag list and free-text notes.
 * Nothing is derived here. The library stores and searches what the Trainer entered; it never computes a
 * Spark firing, an affinity payout or an offspring (FR-G-4, ADR-0020 §3).
 *
 * `training_run_id` is unique because one run is one Veteran: saving a career a second time rewrites its
 * tags and notes rather than growing a duplicate library row for the same run. It cascades on delete,
 * because every fact a Veteran shows lives on the run it points at — with the run gone there is nothing
 * to read back, and an orphan row would render as an empty record. That is the same lifetime `turn_entries`
 * and `race_entries` already have (ADR-0003). It is not an engine write: the run's own `is_manual`
 * protection (FR-B-4) is untouched and no `data_sources` provenance row is written, because a Veteran is
 * Trainer data and not a fetched fact.
 *
 * `tags` is a json list of strings and `notes` is free text, both nullable: a Veteran saved with no tags
 * and no notes is still a complete record. The distance, surface, style and stat facets the library filters
 * on are the Trainer's own tags (design-2.0 SCREEN-020 lists them as the suggested tag vocabulary), so no
 * facet column is added beside `tags`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->unique()->constrained('training_runs')->cascadeOnDelete();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterans');
    }
};
