<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turn_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained('training_runs')->cascadeOnDelete();
            $table->unsignedInteger('turn');
            $table->unsignedSmallInteger('speed');
            $table->unsignedSmallInteger('stamina');
            $table->unsignedSmallInteger('power');
            $table->unsignedSmallInteger('guts');
            $table->unsignedSmallInteger('wit');
            $table->unsignedSmallInteger('sp')->nullable();
            $table->string('condition')->nullable();
            $table->unique(['training_run_id', 'turn']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turn_entries');
    }
};
