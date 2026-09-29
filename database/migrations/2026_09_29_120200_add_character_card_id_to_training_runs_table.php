<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            /*
             * Nullable and additive on purpose (FR-C-1 as amended by ADR-0008). A run
             * created before the card layer, or one where the Trainer named only the
             * trainee, is not a run missing data it should have had.
             *
             * Which key it targets is settled by precedent rather than preference:
             * `2026_09_28_191829` points `race_entries.race_catalog_slot_id` at `race_catalog_slots.id`
             * with `foreignId()->constrained()` even though that table also carries a source-side
             * unique grain. A foreign key names the row, the source id stays the thing a fetch
             * matches on, and the local id is durable because Task 7's store upserts by `card_id`
             * instead of recreating rows -- so `Rule::exists('character_cards','id')` in the run
             * request and `selectionId` in Task 12's payload all read the same key.
             *
             * The same precedent sets the delete rule, and it is a rule rather than a
             * one-off: of every nullable attribution pointer in this repo that states a delete
             * rule at all, every one of them nulls. Four, all measured here rather than
             * recalled --
             * `2026_09_26_162820_create_match_candidates_table.php:21`
             * (`match_candidates.suggested_umamusume_id`),
             * `2026_09_27_093950_create_race_entries_table.php:17`
             * (`race_entries.scenario_race_id`),
             * `2026_09_27_153416_create_scenario_slots_table.php:66-71`
             * (`race_entries.scenario_slot_id`) and
             * `2026_09_28_191829_add_race_catalog_slot_id_to_race_entries.php:30-34`
             * (`race_entries.race_catalog_slot_id`) -- each pair `->nullable()` with
             * `->nullOnDelete()`, and no FK in the directory states a restricting rule. So
             * `nullOnDelete()` is what consistency asks for here, not an
             * exception carved out for this column; semantics agree. `character_cards` is
             * engine-owned reference data that a later fetch or correction may delete, and
             * under a RESTRICT that deletion fails against a Trainer's run row -- engine
             * bookkeeping blocking on, or destroying, Trainer data. Nulling drops only the
             * optional "which form" attribution: the run, its `umamusume_id`, its turns and
             * its skill states survive, the direction every other Trainer-data-preservation
             * rule in this repo runs.
             *
             * Three other FK shapes in the tree are not counterexamples. Owned child rows cascade
             * (`turn_entries.training_run_id` at `2026_09_26_162818_create_turn_entries_table.php:15`,
             * `character_cards.umamusume_id` at `2026_09_29_120100_create_character_cards_table.php:34`) --
             * deleting the parent must delete the row that exists only for it, the opposite obligation from
             * an attribution pointer. `run_skills.skill_id` at
             * `2026_09_26_164640_create_run_skills_table.php:16` is the named counterexample, not a third
             * rule: a Trainer's skill state aimed at engine-owned `skills`, cascading, unable to null (it is
             * half of a composite primary key), so it is only arguably a row existing only for the skill.
             * `database/seeders/SkillSeeder.php:66` does delete skills, so this is a live pre-existing gap
             * this column neither copies nor fixes. Three columns state no rule and take the schema default:
             * `training_runs.umamusume_id` at `2026_09_26_162817_create_training_runs_table.php:16`
             * (required, so a rule that could null it is meaningless) and this table's
             * `inheritance_parent_a_id` / `inheritance_parent_b_id` at `:19-20`. This column follows those
             * four, whose rule is the one that protects a Trainer's run.
             */
            $table->foreignId('character_card_id')
                ->nullable()
                ->constrained('character_cards')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('character_card_id');
        });
    }
};
