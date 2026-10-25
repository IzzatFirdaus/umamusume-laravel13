<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per ADR-0029 D4: run × skill -> level integer, the level and nothing
 * else. No percentage, no hint-pool import, no rarity re-derivation
 * (ADR-0011, ADR-0005 R37). Acquisition status and the turn acquired
 * stay on `run_skills`; this table stores only the level the skill
 * reached in the run (unique-skill level 1-6, UMAMUSUME_REFERENCE.md
 * §1.1.4). Unique on (training_run_id, skill_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained('training_runs')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->unsignedSmallInteger('current_level');
            $table->timestamps();
            $table->unique(['training_run_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_levels');
    }
};
