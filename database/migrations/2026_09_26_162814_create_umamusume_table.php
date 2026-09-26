<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umamusume', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_ja')->nullable();
            $table->string('match_key')->nullable()->index();
            $table->string('release_status')->default(ReleaseStatus::GlobalReleased->value);
            $table->date('jp_debut_date')->nullable();
            $table->date('global_debut_date')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umamusume');
    }
};
