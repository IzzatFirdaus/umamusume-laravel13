<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The normalized search key for a skill's Japanese name (Phase 7, choice A).
 *
 * `match_key` is built from `name`, which on a JapanOnly row is the export's `enname` — a rendering
 * the Air Messiah ruling bars from UI copy. So the one string a JapanOnly row actually prints
 * (`name_ja`) had no searchable key at all, and `?scope=japan_only&search=` could reach a row only
 * through `description_match_key`, a channel whose text that scope refuses to render.
 *
 * The column is nullable and has no default because "no `name_ja` recorded" is a distinct state from
 * "recorded and normalizes to empty", the distinction `description_match_key` already keeps.
 *
 * **No backfill here.** The value is the parser's to write, through `StoreSkills`, and a SQL
 * approximation of the fold inside SQLite would be a second implementation that drifts the moment the
 * normalizer changes. The rows are filled by re-running the import, which is how `match_key` itself
 * was populated.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('match_key_ja', 255)->nullable()->after('match_key')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * The index is dropped in its own call before the column: SQLite will not drop a column an index
     * still references, and `dropColumn` does not cascade to the index it was created with.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropIndex(['match_key_ja']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('match_key_ja');
        });
    }
};
