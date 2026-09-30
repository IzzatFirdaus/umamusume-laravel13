<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two skill lists a trainee's own card publishes (KI-33; PRD FR-A-6, ADR-0008 at card grain).
 *
 * `GametoraCharacterCardParser` reads `card_id`, `char_id`, `title_en_gl`, `rarity` and `release_en`
 * and dropped every `skills_*` key, so nothing in the schema recorded which skills belong to a
 * trainee. That is why `run_skills` cannot be pre-populated and why D-44's `Suggested` state —
 * the state a Trainer sets before the run happens — had no data to be seeded from.
 *
 * **Both columns are json lists, not scalars.** `KNOWN-ISSUES.md` KI-33 measured this on the live
 * `character-cards` document at hash `e9e9ee6d` (268 records): every record carries exactly three
 * innate ids, and **22 records carry two uniques** — card `100701` holds `10071` and `100071`. A
 * nullable `skills_unique_id` scalar would have stored one of them and silently lost the other, and
 * nothing downstream could tell the difference, because the row would look complete.
 *
 * The values stored are the source's own integer export ids, kept as the list arrives so the
 * parser's read and the column's shape cannot disagree. They resolve against `skills.export_id`,
 * which KI-33 verified joins 1,513/1,513 — this is a storage change, not a key-matching fix.
 *
 * **Nullable, and the `array` cast does not coerce a null.** A card the document gives no lists for
 * is a real card, and every row already in the table is such a row until the next fetch writes the
 * lists. So the read side treats null as "nothing to seed", which `TrainingRunTest` pins rather than
 * leaving to the cast's behaviour.
 *
 * No index: these columns are read for one card at run creation, never filtered on. And neither is
 * indexed here, which is what lets `down()` drop them on SQLite — an indexed column cannot be
 * dropped without a table rebuild on this driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->json('skills_innate')->nullable();
            $table->json('skills_unique')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->dropColumn(['skills_innate', 'skills_unique']);
        });
    }
};
