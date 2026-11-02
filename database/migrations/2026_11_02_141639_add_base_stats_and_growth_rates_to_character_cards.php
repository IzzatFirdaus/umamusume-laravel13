<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->json('base_stats')->nullable()->after('rarity');
            $table->json('growth_rates')->nullable()->after('base_stats');
        });
    }

    public function down(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->dropColumn(['base_stats', 'growth_rates']);
        });
    }
};
