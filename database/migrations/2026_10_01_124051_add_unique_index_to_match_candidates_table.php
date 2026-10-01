<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite and MySQL both treat NULLs as distinct inside a unique index,
        // so a plain Blueprint unique on these columns would enforce nothing at all
        // on rows where external_ref is NULL. The IFNULL expression keeps the
        // column itself NULL while still collapsing duplicates.
        DB::statement(
            'CREATE UNIQUE INDEX match_candidates_source_external_match_unique ON match_candidates '.
            "(source_key, IFNULL(external_ref, ''), proposed_match_key)"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'DROP INDEX IF EXISTS match_candidates_source_external_match_unique'
        );
    }
};
