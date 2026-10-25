<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-stat team ranks per §1.8 (UMAMUSUME_REFERENCE.md §2.2.2). Each
 * value is one of G/F/E/D/C/B/A/S; `S+` is the overall team rank
 * (added 2026-07-01) and must never appear in this column. Facility
 * level mapping: F/G→1, D/E→2, B/C→3, A→4, S→5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('team_stat_grades')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('team_stat_grades');
        });
    }
};
