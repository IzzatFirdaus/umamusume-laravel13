<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transfer Requests for Veteran Umamusume (Global notice 1157, 2026-11-05).
 *
 * A transfer request is a Trainer's offer to transfer a filed Veteran for rewards
 * (Star Pieces, Monies, Support Points). The transferred Umamusume disappears
 * permanently. Requests update every 2 days; limited transfer counts apply.
 *
 * This table owns the request lifecycle (offered -> completed | withdrawn) and the
 * recorded reward. It does NOT fulfill the reward (no wallet/ledger exists yet);
 * the reward is recorded on the request for audit.
 *
 * A Veteran can have at most one offered transfer request at a time (unique
 * constraint on veteran_id where status = 'offered').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('veteran_id')
                ->nullable()
                ->constrained('veterans')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('status')
                ->default('offered')
                ->comment('offered, completed, withdrawn');
            $table->json('reward')
                ->nullable()
                ->comment('Recorded reward: {star_pieces?, monies?, support_points?}');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            // One request per Veteran per status (offered/completed/withdrawn)
            $table->unique(['veteran_id', 'status'], 'one_per_veteran_per_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
