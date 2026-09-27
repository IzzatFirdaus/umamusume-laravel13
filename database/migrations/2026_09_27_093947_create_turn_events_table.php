<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turn_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained('training_runs')->cascadeOnDelete();
            $table->unsignedSmallInteger('turn');
            $table->string('event_type', 30);
            $table->string('source_name');
            $table->unsignedTinyInteger('choice_index')->nullable();
            $table->string('choice_label')->nullable();
            // Nullable because an event may fire with no consequence to record.
            // Observed outcomes only: turn_entries stays the source of truth for
            // absolute values, so a mismatch is a defect, not a second opinion.
            $table->json('deltas')->nullable();
            $table->string('support_card_name')->nullable();
            $table->smallInteger('bond_delta')->nullable();
            $table->text('origin_note')->nullable();
            $table->timestamps();

            $table->index(['training_run_id', 'turn']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turn_events');
    }
};
