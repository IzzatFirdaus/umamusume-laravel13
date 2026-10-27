<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UMAMUSUME_REFERENCE.md §1.1.7: the completion payload. The mode's per-turn algorithm is not
 * published, so the simulator (Slice 1.3) is a calibrated approximation, and this column stores the
 * result it produces at completion — the completion screen reads it rather than re-running the
 * simulator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('independent_training_runs', function (Blueprint $table): void {
            $table->json('result')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('independent_training_runs', function (Blueprint $table): void {
            $table->dropColumn('result');
        });
    }
};
