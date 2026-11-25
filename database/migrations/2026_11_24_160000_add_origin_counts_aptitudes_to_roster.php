<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Member identity, coverage and aptitude observations on the recorded roster (UC3.2, UC3.3, UC3.4).
 *
 * **`origin` (UC3.2).** §1.9 separates three kinds of teammate: the deck's six characters join as
 * themselves, the trainee is one of the twenty, and the rest arrive as Guest R cards tagged
 * `[Tracen Academy]`. The row so far recorded only `is_trainee`, which cannot say whether a member
 * came from the deck or off a guest card, and the frontend cannot show an origin badge without the
 * fact. The value is one of `trainee`, `support`, `guest`, `unknown` — `unknown` is the honest arm
 * for a member the Trainer added by name without recording where she came from. It is a plain
 * string column, not an enum table: the case list lives on `RosterMember` alongside the other
 * vocabulary this table owns.
 *
 * **Support-card linkage (UC3.2).** A support-derived member joined the team because the Trainer
 * equipped a specific *card*, and character identity is not card identity — Tokai Teio `[Dream
 * Big!]` and any other Teio card are different support cards. The link therefore points at
 * `support_cards.id`, nullable, `nullOnDelete` like `umamusume_id`: deleting the card row leaves
 * the member and her recorded stat line, losing only the pointer. The seeder binds the six deck
 * characters to the exact cards §1.4 lists.
 *
 * **Observed roster count (UC3.3).** §1.8's Team Info panel reads `Members: 20 / 20` while §1.9
 * names only eighteen: seventeen teammates plus the trainee. The observed count and the recorded
 * identities are two different facts, and collapsing them either invents two members or throws the
 * client's reading away. `observed_member_count` stores what the client said, per run, nullable
 * because an unobserved run has no reading (D-220); the recorded count stays a `COUNT(*)` the
 * read-out derives. Adding or deleting a member row never rewrites the observation.
 *
 * **Aptitude observations (UC3.4).** A teammate's card shows her own ten aptitude letters, and
 * §1.9's "Aptitudes of note" lines are partial: they name the letters worth reading out, not all
 * ten. Each column is nullable, so a partial observation stays partial and a member with no
 * observation has ten nulls, not ten guesses. They are stored on the member, separate from the
 * catalogue's committed defaults on `umamusume` — the catalogue is what the source publishes and
 * the observation is what the client showed, and §1.2's erratum D8 is the standing example of the
 * two disagreeing without either side being wrong. The column names reuse
 * `Umamusume::APTITUDE_AXES` keys verbatim so a reader can map one to the other mechanically.
 */
return new class extends Migration
{
    public function up(): void
    {
        // `origin` is the only non-nullable column this migration adds, so a partially applied run
        // (a failed statement inside a later block) would block a retry with `duplicate column`.
        // The guard makes the migration re-runnable without hiding a real failure. The FK pointer
        // gets the same guard for the same reason: SQLite cannot DROP a column named in a foreign
        // key (see `down()`), so after one down() cycle `support_card_id` survives and a bare
        // re-run would die on `duplicate column name`. Idempotent guards are the honest shape for
        // an additive migration that a rollback cannot fully undo.
        $existing = collect(Schema::getColumns('roster_members'))->pluck('name')->all();

        if (! in_array('origin', $existing, true)) {
            Schema::table('roster_members', function (Blueprint $table): void {
                $table->string('origin')->default('unknown')->after('name');
            });
        }

        if (! in_array('support_card_id', $existing, true)) {
            Schema::table('roster_members', function (Blueprint $table): void {
                $table->foreignId('support_card_id')->nullable()->after('umamusume_id')
                    ->constrained('support_cards')->nullOnDelete();
            });
        }

        $runColumns = collect(Schema::getColumns('training_runs'))->pluck('name')->all();

        // UC3.3: the client's own `Members: N / 20` reading lives on the run, not the member.
        if (! in_array('observed_member_count', $runColumns, true)) {
            Schema::table('training_runs', function (Blueprint $table): void {
                $table->unsignedTinyInteger('observed_member_count')->nullable()->after('team_preseason_wins');
            });
        }

        $aptitudes = array_values(array_filter($existing, static fn (string $c): bool => str_starts_with($c, 'aptitude_')));

        if ($aptitudes === []) {
            Schema::table('roster_members', function (Blueprint $table): void {
                // UC3.4: the ten observed aptitude letters. Same keys as `Umamusume::APTITUDE_AXES`.
                $table->string('aptitude_turf', 1)->nullable();
                $table->string('aptitude_dirt', 1)->nullable();
                $table->string('aptitude_sprint', 1)->nullable();
                $table->string('aptitude_mile', 1)->nullable();
                $table->string('aptitude_medium', 1)->nullable();
                $table->string('aptitude_long', 1)->nullable();
                $table->string('aptitude_front_runner', 1)->nullable();
                $table->string('aptitude_pace_chaser', 1)->nullable();
                $table->string('aptitude_late_surger', 1)->nullable();
                $table->string('aptitude_end_closer', 1)->nullable();
            });
        }
    }

    public function down(): void
    {
        // A half-completed rollback must be re-runnable. If a previous `migrate:rollback`
        // succeeded through the plain-column drops and then failed, `origin` and
        // `support_card_id` are in different states; `hasColumn` guards each drop so a retry
        // finishes the job instead of dying on the first name that is already gone.
        //
        // `support_card_id` cannot leave by ALTER while its foreign-key definition stands (SQLite
        // 3.35+; the same limit the `140000` repair migration records), so the pointer column
        // stays: `down()` drops what ALTER can drop and leaves the pointer, which is forward-only
        // in the sense the repair migration documents — rolling back removes the readings but not
        // the linkage the records were written through.
        //
        // ponytail: a full create-rename-drop-rename rebuild would remove the pointer too; copy
        // `140000`'s sequence if a real need for that appears.
        $drops = [
            'roster_members' => [
                'aptitude_turf', 'aptitude_dirt', 'aptitude_sprint', 'aptitude_mile', 'aptitude_medium',
                'aptitude_long', 'aptitude_front_runner', 'aptitude_pace_chaser', 'aptitude_late_surger',
                'aptitude_end_closer', 'origin',
            ],
            'training_runs' => ['observed_member_count'],
        ];

        foreach ($drops as $table => $columns) {
            $existing = Schema::hasTable($table)
                ? collect(Schema::getColumns($table))->pluck('name')->all()
                : [];

            $remaining = array_values(array_intersect($columns, $existing));

            if ($remaining !== []) {
                Schema::table($table, fn (Blueprint $t): mixed => $t->dropColumn($remaining));
            }
        }
    }
};
