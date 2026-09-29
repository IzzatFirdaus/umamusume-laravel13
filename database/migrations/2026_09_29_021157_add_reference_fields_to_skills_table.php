<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The columns the skills reference import needs (ADR-0011; PRD FR-D-1 as amended 2026-09-29).
 *
 * Purely additive on a table that currently holds ten names. Nothing here restates a decision:
 * `docs/adr/0011-skills-reference-import.md` carries the measurements, and every column below is the
 * consequence of one of them.
 *
 * **`rarity` stores the export's integer and is never a client word.** The source carries six class
 * values (1, 2, 3, 4, 5, 6) while the client's rarity set is three, and the six encode *learnable vs
 * unique vs evolved* rather than Normal/Rare/Unique: cost is present on 1 and 2 only, `char` binds 3/4/5,
 * and all 672 rows of 6 carry `pre_evo`. A column that displayed "Rare" off this field would repeat the
 * tier-label defect R65 already corrected one table over, so `race_catalog_slots`' split is copied here:
 * the code stays auditable, and a label appears only where a source pins it.
 *
 * **`release_status` is nullable, which `umamusume`'s is not.** `umamusume` could default to
 * `GlobalReleased` because its rows arrive with a dated `release_en` field to read. The ten rows already
 * in `skills` were seeded from a publisher's skill list and no source has stated their availability, and
 * `ADR-0004`'s rule is that an unattributed fact stays null rather than taking a plausible default: a
 * default here would print "JapanOnly" or "GlobalReleased" over a row nobody has attributed either way.
 * The importer always sets it, because the export states it (`unreleased`, an array of server codes whose
 * every populated value contains `en`; absence is the Global statement, 623 rows).
 *
 * **`name_is_client` is what makes storing all 1,910 rows safe.** `PRD.md` D-3 records availability at
 * write time and applies it at read time, so 925 rows arrive with no client English name at all and 362
 * of the unreleased ones carry an English string that is not client copy either — `UMAMUSUME_REFERENCE.md`
 * §2.7's Air Messiah ruling is exactly this case ("an English string on a unit with no `start_en` is a
 * third-party label"). `name_en` is the client string and `enname` is a literal rendering of the Japanese;
 * they differ on 535 of the 623 Global rows, so the flag is set from the pair the source states
 * (`name_en` present **and** released on Global), never from which field looked English.
 *
 * `source_url`, `snapshot_path`, `fetched_at`, `source_timezone` are `ADR-0003` Amendment R3's four
 * provenance columns, named as `race_catalog_slots` names them, and `is_manual` is PRD FR-B-4's stop sign:
 * a skill row a Trainer wrote is never overwritten by the engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table): void {
            // The export's own numeric skill id. Unique, and nullable so the ten
            // seeded rows and any Trainer row survive before an import attributes them.
            $table->unsignedInteger('export_id')->nullable()->unique();

            $table->unsignedTinyInteger('rarity')->nullable();
            $table->string('release_status', 20)->nullable();
            $table->boolean('name_is_client')->default(false);

            $table->string('source_url')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->dateTime('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->boolean('is_manual')->default(false);

            // The read path every Trainer-facing surface starts from: released on
            // Global, and named by the client. Indexed because D-3 makes it a filter
            // applied on each read rather than a condition applied once at import.
            $table->index(
                ['release_status', 'name_is_client'],
                'skills_release_status_name_is_client_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table): void {
            $table->dropIndex('skills_release_status_name_is_client_index');
            $table->dropUnique('skills_export_id_unique');

            $table->dropColumn([
                'export_id',
                'rarity',
                'release_status',
                'name_is_client',
                'source_url',
                'snapshot_path',
                'fetched_at',
                'source_timezone',
                'is_manual',
            ]);
        });
    }
};
