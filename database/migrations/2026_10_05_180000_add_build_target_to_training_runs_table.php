<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One nullable json column holding the Trainer's build target (FR-F-1, `ADR-0020` §2, plan §7 C1).
 *
 * A column rather than a table, and nullable rather than defaulted: a run with no target is the
 * ordinary first state, and the advisor's contract distinguishes "no target entered" from "a target
 * entered with nothing in it" (`BuildTargetPayload`). A default of `{}` would erase that difference
 * at the schema layer, where it is hardest to see.
 *
 * Nothing is indexed. The library slice searches Veterans by their own filters, and no read path
 * queries *by* a target; adding an index here would be a guess about a query that does not exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('build_target')->nullable()->after('shop_resets_in');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('build_target');
        });
    }
};
