<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The failure rate and the preview gains the client shows before a turn resolves, from the UX walk's
 * Phase 12.
 *
 * The audit's screen 9 recorded `Failure 39%` and `+18 Speed / +8 Power / +6 Skill Points` with
 * nowhere to put them. Both are **readings the Trainer transcribes**, never figures this tool
 * computes: `ADR-0016` and `ADR-0020` §3 keep outcome prediction out of the product, and `PRD.md` §6.11
 * is why the advisor's answer must not move when these are set.
 *
 * `failure_rate` is a bounded percentage, so it is a column with a ceiling rather than a JSON member.
 * `preview_gains` is six values keyed by stat and skill points, which is a payload rather than six
 * columns: the set is the client's own preview row, and a seventh reading would be a schema change
 * rather than a new column.
 *
 * Null means the Trainer did not read either this turn, which is the ordinary case for a turn logged
 * from the rail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->unsignedTinyInteger('failure_rate')->nullable();
            $table->json('preview_gains')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropColumn(['failure_rate', 'preview_gains']);
        });
    }
};
