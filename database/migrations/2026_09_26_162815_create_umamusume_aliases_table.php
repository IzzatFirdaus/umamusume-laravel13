<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umamusume_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete();
            $table->string('alias');
            $table->string('language');
            $table->unique(['alias', 'language']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umamusume_aliases');
    }
};
