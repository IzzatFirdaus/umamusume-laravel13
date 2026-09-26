<?php

declare(strict_types=1);

use App\Enums\SkillAcquisition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_skills', function (Blueprint $table): void {
            $table->foreignId('training_run_id')->constrained('training_runs')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('status')->default(SkillAcquisition::Suggested->value);
            $table->unsignedInteger('turn_acquired')->nullable();
            $table->primary(['training_run_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_skills');
    }
};
