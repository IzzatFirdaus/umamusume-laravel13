<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Unity Cup team roster (D01): up to 20 actual team members, recorded per run.
 *
 * §1.7 of `docs/research/flows-career-workspace.md` names this as the roster gap, and §1.8-1.9 of
 * `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` state what the client holds: a Team Info panel
 * reading `Members: 20 / 20`, and a Team tab that lays the roster out as five distance columns
 * (Sprint, Mile, Medium, Long, Dirt) with an **ACE** slot plus two more in each — the
 * one-to-three-per-distance rule at `docs/scenarios/02-unity-cup.md:170`.
 *
 * **This is a recorded roster, not a derived one.** Until now `CockpitController::rosterSection()`
 * read the six `deck_slots` and called the result "the derived team roster", which is a different
 * fact: the deck is six support cards the Trainer equipped, and the roster is the twenty characters
 * who train alongside the trainee. §1.9 records that the deck characters appear as teammates while
 * the rest arrive as Guest R cards tagged `[Tracen Academy]`, so the deck is a *subset* of the roster
 * and cannot stand in for it. The deck derivation is retired from the Cockpit roster readout and the
 * deck itself is untouched (ADR-0014).
 *
 * **The trainee is one of the twenty.** §1.8's ACE row names the trainee herself as the Long ACE, so
 * the count includes her; `is_trainee` marks which row is hers rather than excluding her from the
 * table, because a roster that omitted its own trainee could not reach the client's `20 / 20`.
 *
 * **What is nullable is unrecorded, never zero (D-220).** A member's stat line, grade, aptitudes and
 * distance assignment are all nullable: a roster row the Trainer added by name has no readings yet,
 * and a fabricated figure would be a claim about a record that does not exist. The member's name is
 * the one required field, because a row with no identity is not a member.
 *
 * `umamusume_id` is nullable for the same reason. The trainee and the deck characters resolve to
 * catalogue rows; the Guest R cards §1.9 names do not all exist in this tool's catalogue, so a
 * recorded guest keeps her name and no catalogue link rather than being dropped.
 *
 * The 20-member ceiling, the one-to-three-per-distance rule and the ACE's uniqueness are validated at
 * the model and Form Request layers: SQLite ignores `->check()` column modifiers (the same note
 * `create_support_cards_and_support_effects_tables` records), so a DB-level constraint would render no
 * SQL at all. `position` gives the roster a stable order for the Team tab's columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained()->cascadeOnDelete();
            // Nullable: a Guest R card §1.9 names may have no catalogue row in this tool.
            // `umamusume` is singular (the model's `#[Table('umamusume')]`), so the FK names it
            // explicitly rather than letting Laravel's default pluralisation look for `umamusumes`.
            $table->foreignId('umamusume_id')->nullable()->constrained('umamusume')->nullOnDelete();
            $table->string('name');
            // The client's own member grade letter, read off the Team tab (`A`, `C`, …). The same
            // vocabulary as the aptitude letters, so it is a char rather than a lookup table.
            $table->char('grade', 1)->nullable();
            $table->unsignedTinyInteger('speed')->nullable();
            $table->unsignedTinyInteger('stamina')->nullable();
            $table->unsignedTinyInteger('power')->nullable();
            $table->unsignedTinyInteger('guts')->nullable();
            $table->unsignedTinyInteger('wit')->nullable();
            // The five distance columns the Team tab lays the roster out in, plus Dirt, which is a
            // surface the `team_race.categories` list names and `BuildTargetPayload::DISTANCE_BANDS`
            // does not. Null means unassigned, which is an absence rather than a sixth distance.
            $table->string('distance_assignment')->nullable();
            $table->boolean('is_ace')->default(false);
            $table->boolean('is_trainee')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            // One row per member per run, and the roster reads in `position` order.
            $table->index(['training_run_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_members');
    }
};
