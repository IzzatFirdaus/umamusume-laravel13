<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * R54: add source_key to scenario_slots so multiple races can share a month+half.
 *
 * The original unique composite (scenario_key, month, half, kind) cannot hold
 * the Early August triple (Cosmos Sho, Dahlia Sho, Phoenix Sho) — all three
 * are goal_race in month 8 Early. source_key distinguishes them and becomes
 * part of the new composite. Seeded rows carry the export's instance id or a
 * slug derived from the title; manual rows carry null.
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->string('source_key')->nullable()->after('kind');
        });

        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->dropUnique('scenario_slots_scenario_key_month_half_kind_unique');
            $table->unique(
                ['scenario_key', 'month', 'half', 'kind', 'source_key'],
                'scenario_slots_composite_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->dropUnique('scenario_slots_composite_unique');
            $table->unique(
                ['scenario_key', 'month', 'half', 'kind'],
                'scenario_slots_scenario_key_month_half_kind_unique',
            );
        });

        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->dropColumn('source_key');
        });
    }
};
