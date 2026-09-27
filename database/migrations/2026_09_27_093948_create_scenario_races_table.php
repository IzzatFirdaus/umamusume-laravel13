<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenario_races', function (Blueprint $table): void {
            $table->id();
            $table->string('scenario_key', 50);
            $table->string('slot_label');
            $table->string('race_name');
            // Tier stores the label as recorded; never a value derived from a
            // grade code. Only 100 = G1 and 400 = OP are client-confirmed.
            $table->string('tier', 10)->nullable();
            $table->unsignedInteger('fans_needed')->nullable();
            $table->boolean('mandatory')->default(false);
            $table->boolean('maiden_gated')->default(false);
            $table->string('source_url')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->timestamps();

            $table->index(['scenario_key', 'slot_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenario_races');
    }
};
