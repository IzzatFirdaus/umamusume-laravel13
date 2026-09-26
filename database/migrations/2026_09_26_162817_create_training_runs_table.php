<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('umamusume_id')->constrained('umamusume');
            $table->string('scenario')->nullable();
            $table->string('status')->default(RunStatus::Active->value);
            $table->foreignId('inheritance_parent_a_id')->nullable()->constrained('umamusume');
            $table->foreignId('inheritance_parent_b_id')->nullable()->constrained('umamusume');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_runs');
    }
};
