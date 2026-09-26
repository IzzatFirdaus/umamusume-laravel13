<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete();
            $table->string('url');
            $table->string('source_key');
            $table->timestamp('fetched_at');
            $table->string('snapshot_path')->nullable();
            $table->float('confidence')->nullable();
            $table->string('source_timezone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};
