<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The canonical career position, from the Snapshot Domain Foundation slice.
 *
 * One JSON column rather than five, for the reason `ADR-0003` chose JSON for the structured run
 * payloads: a position is only ever read as a whole, and nothing in the planning phases filters a run
 * by its month. Splitting it would put five half-populated columns beside a payload that always
 * arrives complete or not at all.
 *
 * `career_position_source` says where a stored position came from - `derived` when a computation was
 * confirmed, `imported` when a snapshot named it - and null means "derive from the turn count", which
 * is every new-career run's answer until something overrides it.
 *
 * Cited requirement: the UX walk's Phase 3 (docs/audits/rice-shower-unity-cup-ux-walk.md), the
 * finding that a mid-career position has no representation. The ADR for the column decision is the
 * owner's to author; this docblock carries the reasoning until then.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('career_position')->nullable();
            $table->string('career_position_source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['career_position', 'career_position_source']);
        });
    }
};
