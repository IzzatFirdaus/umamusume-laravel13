<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The trainee's rarity (1..3) and potential level (1..5), as the Trainer reads them off the run.
 *
 * Both are entered, never derived: the card layer stores its own rarity, but a run may name only a
 * trainee and no card, and the potential level is a property of the individual trainee rather than
 * of the card, so nothing here can compute either. Nullable, because a run that has not stated one
 * renders the N/A disclosure rather than a default (D-220).
 *
 * Cited requirement: the UX walk's trainee-select screen and build-target wizard both list "★3 and
 * Potential Lvl 2" as source data with no UI home (docs/audits/rice-shower-unity-cup-ux-walk.md
 * lines 122 and 181). Persistence only; the cockpit identity render is a later slice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedTinyInteger('trainee_rarity')->nullable();
            $table->unsignedTinyInteger('potential_level')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['trainee_rarity', 'potential_level']);
        });
    }
};
