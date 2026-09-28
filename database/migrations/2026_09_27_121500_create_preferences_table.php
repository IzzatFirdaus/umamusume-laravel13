<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trainer UI preferences, one row per key (PRD US-11, authorized 2026-09-27).
 *
 * SQLite, not browser storage, because PRD §6 non-goal 12 cuts a second source of
 * truth — the ruling behind research D-104 and ADR-0001's off-by-default
 * failure-estimate toggle. The table is the store; nothing here may be mirrored
 * to `localStorage`.
 *
 * There is no `user_id`. One Trainer, one machine is a Phase 1 non-goal (§6.1),
 * so a keyed single-tenant table is the whole shape. `key` is the primary key:
 * a preference that has never been set has no row, and that absence is the
 * default rather than a NULL to interpret.
 *
 * Authorized keys are `theme` and `failure_estimate`. A third key is a PRD
 * change, not a migration — US-7's display timezone is owned by its own story
 * and does not arrive through this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preferences', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->text('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
