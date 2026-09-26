<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Training-scenario reference data: the per-stat cap each scenario enforces on a
 * run, and the database hard cap above it (ADR-0004, PRD FR-A-5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_ja')->nullable();
            $table->unsignedTinyInteger('order')->nullable();
            $table->date('jp_start_date')->nullable();
            $table->date('global_start_date')->nullable();
            $table->unsignedSmallInteger('cap_speed');
            $table->unsignedSmallInteger('cap_stamina');
            $table->unsignedSmallInteger('cap_power');
            $table->unsignedSmallInteger('cap_guts');
            $table->unsignedSmallInteger('cap_wit');
            $table->unsignedSmallInteger('hard_cap')->nullable();
            // The date [Global] raised its scenario caps. Null means the source
            // does not state one for this scenario, not that no rework happened.
            $table->date('caps_reworked_at')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
