<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_candidates', function (Blueprint $table): void {
            $table->id();
            $table->string('source_key');
            $table->string('external_ref')->nullable();
            $table->string('proposed_name');
            $table->string('proposed_name_ja')->nullable();
            $table->string('proposed_match_key')->nullable();
            $table->foreignId('suggested_umamusume_id')->nullable()->constrained('umamusume')->nullOnDelete();
            $table->string('match_tier');
            $table->string('status')->default(CandidateStatus::Pending->value);
            $table->json('payload');
            $table->timestamp('created_by_fetch_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_candidates');
    }
};
