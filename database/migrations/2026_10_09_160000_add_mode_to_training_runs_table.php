<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The mode a run came to exist in, from the Snapshot Domain Foundation slice.
 *
 * `mode` is nullable in the schema and backfilled here rather than defaulted in the schema, so the
 * backfill is a visible one-time statement about existing rows instead of a default that would also
 * answer a row some future writer forgot to set. Every row that exists today is a new career: the
 * snapshot entry point this column serves does not exist yet, so no row can be anything else, and
 * saying so in data rather than leaving null keeps `isSnapshot()` a plain read.
 *
 * Cited requirement: the UX walk's Phase 3 (docs/audits/rice-shower-unity-cup-ux-walk.md), the
 * mid-career position finding. The ADR is the owner's to author.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->string('mode')->nullable()->index();
        });

        DB::table('training_runs')->whereNull('mode')->update(['mode' => 'new_career']);
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropIndex(['mode']);
            $table->dropColumn('mode');
        });
    }
};
