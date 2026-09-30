<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The trainee profile block, one row per trainee (the "basic information" source,
 * `characters.c6676539` in the GameTora manifest).
 *
 * **Why a sibling table and not six columns on `umamusume`.** `umamusume` carries no inline
 * provenance of its own, and ADR-0003 Amendment R3 requires `source_url`, `fetched_at` and the
 * rest on a reference row. Six columns on `umamusume` would have had to borrow the trainee's
 * `data_sources` row, which is scoped to the character fetch rather than to this document, so
 * "where did this height come from" would have had no row that answers it — the same reasoning
 * `character_cards` records for not borrowing its parent's provenance.
 *
 * **It is 1:1, not 1:N.** The source is keyed by `char_id`, one row per trainee, and
 * `umamusume.external_ref` holds exactly one such ref per row (measured: 135 roster rows, 135
 * refs, 0 duplicates). The unique constraint is what makes a re-fetch idempotent by identity
 * (PRD FR-B-5) and is why the store action can `updateOrCreate` on `umamusume_id`.
 *
 * **Every field is nullable, deliberately.** The document is 163 rows against 135 trainees, so
 * 28 of its rows are for characters this catalog does not track, and the roster rows it does
 * cover are not uniformly complete: measured over the 135, `va_en` and `three_sizes` are each
 * absent on 10, while `jp_name`, `va_ja`, `height` and all three birthday parts are present on
 * all 135. A NOT NULL column would have forced the parser to invent a value or drop the trainee;
 * nullable plus D-220 is the honest shape, and the view renders absence in words.
 *
 * **`birth_*` is three columns because the source is three columns.** The document has no `birth`
 * field: it has `birth_year`, `birth_month` and `birth_day`, and only `birth_year` is ever null
 * (17 of 163). Storing one assembled date would have had to invent a year for those 17, so the
 * parts are kept apart and the view decides what a partial birthday reads as.
 *
 * **`three_sizes` is three columns because the source is an object.** The document's
 * `three_sizes` is `{"b": 81, "h": 81, "w": 56}`, not a string; the earlier probe that reported a
 * space-separated string was reading a rendering, not the body. A json column would hide three
 * integers behind a cast nobody else in this tree uses, and a string column would have re-parsed
 * something the source already hands over structured.
 *
 * **`is_manual` is this table's own flag** (PRD FR-B-4), the same reasoning `character_cards`
 * records: a Trainer correcting a height has not claimed the whole trainee's data.
 *
 * `sex` and `race` are deliberately not columns. The document carries both, neither is one of the
 * six fields the profile block shows, and CONSTRAINTS.md C-4 governs what this tool calls these
 * characters — so a column nothing renders is the one place that rule would be easiest to breach.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umamusume_profiles', function (Blueprint $table): void {
            $table->id();
            // Unique, not just indexed: the source is one row per trainee, and this is what
            // makes a re-fetch update its own row rather than accumulate a second profile
            // per character (PRD FR-B-5).
            $table->foreignId('umamusume_id')->unique()->constrained('umamusume')->cascadeOnDelete();
            // The source's `jp_name`. `umamusume.name_ja` already holds a Japanese name from the
            // card document, so this is a second source for a fact we already have; the view
            // reads this row and does not fall back, because one source filling another's gap
            // silently is the defect D-220 exists to prevent.
            $table->string('name_ja')->nullable();
            // `va_ja` carries the Japanese credit (present on all 163 rows); `va_en` is its
            // romanisation, absent on 26 of 163 file-wide and on 3 of the 105 race === 'uma' rows the
            // parser keeps. They name one performer in two scripts, not two casts — where a stage name
            // is already romanised both fields hold the identical string. Both are stored because the
            // Global page wants the romanisation and the source is complete in Japanese only.
            // [Dated erratum 2026-09-30: this comment first read va_en as "the English dub cast" that
            // "disagrees" with va_ja. The body refutes it; see GametoraCharacterProfileParser, A-7 and
            // docs/data/2026-09-30-characters-source-probe.md.]
            $table->string('va_ja')->nullable();
            $table->string('va_en')->nullable();
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->unsignedTinyInteger('birth_month')->nullable();
            $table->unsignedTinyInteger('birth_day')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            // bust / height / waist, in the source's own key order. Centimetres, unsigned
            // 135..158 measured, so smallint has ample room and no unit column is needed.
            $table->unsignedSmallInteger('three_sizes_b')->nullable();
            $table->unsignedSmallInteger('three_sizes_h')->nullable();
            $table->unsignedSmallInteger('three_sizes_w')->nullable();
            // Inline provenance, ADR-0003 Amendment R3, the same four every reference table in
            // this tree carries.
            $table->string('source_url');
            $table->string('snapshot_path')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umamusume_profiles');
    }
};
