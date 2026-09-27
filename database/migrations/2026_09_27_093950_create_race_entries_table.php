<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained('training_runs')->cascadeOnDelete();
            // Nullable because a free-form race has no calendar slot.
            $table->foreignId('scenario_race_id')->nullable()->constrained('scenario_races')->nullOnDelete();
            $table->string('status', 20);
            $table->unsignedTinyInteger('placement')->nullable();
            $table->unsignedInteger('fans_gain')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_entries');
    }
};
