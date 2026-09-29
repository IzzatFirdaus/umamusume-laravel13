<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * D-268: Legacy Select shows more than `training_runs` can hold.
 *
 * The screen reports, per Legacy: the chosen Umamusume, its rank, whether it is a Guest, its own
 * two ancestors, and a Spark list with per-Spark kind, target and star rank — plus an affinity grade
 * for the pair. The run carried two character ids, which is why D-268 says Phase 4's provenance
 * requirement is "not met by the existing columns" and asks for "a typed json payload keyed to the
 * run rather than a wide table of nullable columns". This is that payload.
 *
 * One column, and the two `inheritance_parent_*` foreign keys stay exactly where they are. The
 * alternative — `legacy_parent_a_id`, `legacy_parent_b_id`, and a spark column per field — was
 * ruled out by the owner for this session: a second pair of names for a relationship the table
 * already models, and nine nullable columns where one keyed payload belongs.
 *
 * Nullable with no default. A run whose Trainer never opened the screen has no read-back, which is
 * a different statement from an empty one, and D-220 requires the difference to survive to the
 * render path.
 *
 * This stores what the client displayed and computes nothing from it. PRD §6 non-goal 3 bans the
 * engine over this data; ADR-0010 records where this column sits relative to that ban.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('legacy_selection')->nullable()->after('inheritance_parent_b_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('legacy_selection');
        });
    }
};
