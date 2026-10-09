<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Energy as a state, not only a number.
 *
 * The numeric `energy` column stays the exact reading. `energy_state` says whether that reading is an
 * exact figure, a coarse band, or absent, and `energy_band` carries the band's own word. Every existing
 * row is backfilled: a row with a number becomes `exact`, a row without one becomes `unknown`, which is
 * the state the audit found the tool treating as a bare N/A everywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->string('energy_state')->nullable()->after('energy');
            $table->string('energy_band')->nullable()->after('energy_state');
        });

        DB::table('turn_entries')->whereNotNull('energy')->update(['energy_state' => 'exact']);
        DB::table('turn_entries')->whereNull('energy')->update(['energy_state' => 'unknown']);
    }

    public function down(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropColumn(['energy_state', 'energy_band']);
        });
    }
};
