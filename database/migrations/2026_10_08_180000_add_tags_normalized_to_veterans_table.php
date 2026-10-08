<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A case-folded twin of `veterans.tags`, so the library's tag filter can answer the suggestion chips it
 * offers (PRD FR-G-2, KI-72).
 *
 * The stored spelling stays the Trainer's own; this companion column carries the same list lowercased,
 * and `ListVeterans` matches filter values against it. SQLite compares JSON string elements
 * case-sensitively (`whereJsonContains`), so before the twin a Veteran tagged by hand as `speed` was
 * unfindable through the library's `Speed` suggestion: the row existed, the filter said it did not, and
 * nothing errored (KI-72).
 *
 * Existing rows are folded on the spot so the filter keeps answering for a library written before this
 * column did. The backfill runs over `DB::table`, not the model: a migration must not depend on a model
 * shape a later commit can move, and the table holds no rows on which it can fail either way.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veterans', function (Blueprint $table): void {
            $table->json('tags_normalized')->nullable();
        });

        DB::table('veterans')->select('id', 'tags')->orderBy('id')->chunkById(200, function (Collection $rows): void {
            foreach ($rows as $row) {
                /** @var object{id: int|string, tags: string|null} $row */
                $tags = json_decode((string) ($row->tags ?? ''), true);

                if (! is_array($tags)) {
                    continue; // no tags recorded: nothing to fold, and null stays null
                }

                DB::table('veterans')->where('id', $row->id)->update([
                    'tags_normalized' => json_encode(array_map(
                        static fn (string $tag): string => mb_strtolower($tag),
                        array_values(array_filter($tags, 'is_string')),
                    )),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('veterans', function (Blueprint $table): void {
            $table->dropColumn('tags_normalized');
        });
    }
};
