<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The activation predicate and effect vector a skill's own record publishes (ADR-0011; PRD FR-D-1).
 *
 * `GametoraSkillsParser` reads eleven keys of the skill record and dropped `condition_groups`, so
 * nothing in the schema recorded *when* a skill fires or *what it does* — the reason the detail page
 * renders a mechanics absence instead of the mechanics. This column is that field, kept as the list
 * the source publishes.
 *
 * **Shape, measured on the committed body (`skills.609afe88.json`, 1,910 records, hash `609afe88`).**
 * A list of one or two groups; each group is `base_time` (ms), `condition` and `precondition` (the
 * engine's own boolean expressions, `&`-joined, `@` separating alternatives), and `effects`, a list of
 * `{type, value}` integer pairs. Non-empty on all 1,910 records, 2,314 groups in total, at most two
 * per skill and 404 skills with two. The parser projects that shape rather than storing the decoded
 * blob, so a body that changes shape fails loudly at parse time instead of landing junk here.
 *
 * **Nullable, and the `array` cast does not coerce a null.** A row the source gives no groups for is a
 * real row, and the read side treats null as the absence it renders in words (D-220).
 *
 * No index: the column is read for one skill on its detail page, never filtered on. That is also what
 * lets `down()` drop it on SQLite, where an indexed column cannot be dropped without a table rebuild.
 *
 * **The values are the source's engine codes, not client labels.** Rendering them is beyond D-30's
 * current `Skill` entry; the widening ask is filed in `docs/research-scratch/PLANS-AND-BRIEFS.md` and
 * is not assumed by this migration, which only stores what the source states.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table): void {
            $table->json('condition_groups')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table): void {
            $table->dropColumn('condition_groups');
        });
    }
};
