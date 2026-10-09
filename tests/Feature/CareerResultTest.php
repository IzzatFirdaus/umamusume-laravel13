<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-019, the Career Result (plan §8 D15). The props are asserted here; the rendered copy,
 * the two empty states, the 44px sweep and the axe scan live in `tests/browser/career-result.spec.ts`.
 *
 * **The owner's four corrections bind here, and each has a named test below.**
 * (1) Four race counts, never two: `race_entries.status` and `placement` are independent facts, so
 *     "ran but the finish was not recorded" is its own number rather than a loss.
 * (2) Two empty states: Active and Retired are different absences with different doors.
 * (3) Save Veteran is a named absence with a reason, never a button, because D16 does not exist.
 * (4) A run with no build target reports no deficit at all. The failure this guards is a
 *     `target ?? 0` coercion, which would print every stat as short by its own value.
 */

function careerResultRun(array $runState = [], int $turns = 0): TrainingRun
{
    $run = TrainingRun::factory()->create($runState + [
        'status' => RunStatus::Completed,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

it('renders the three sections for a completed run', function (): void {
    $run = careerResultRun(['scenario' => 'trackblazer'], turns: 2);

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Result')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.status', 'Completed')
            ->where('run.status_label', 'Completed')
            ->where('run.scenario_label', 'Trackblazer')
            ->where('run.run_url', route('runs.cockpit', $run))
            ->where('run.cockpit_url', route('runs.cockpit', $run))
            ->where('run.timeline_url', route('runs.timeline', $run))
            ->has('build.stats', 5)
            ->has('races.counts')
            ->has('scenario')
            ->where('empty', null)
        );
});

it('(2) gives an Active run its own empty state, with the Cockpit as the way back', function (): void {
    $run = careerResultRun(['status' => RunStatus::Active], turns: 1);

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('empty.reason', 'active')
            ->where('empty.message', fn (string $message): bool => str_contains($message, 'still running'))
            ->where('build.stats', [])
            ->where('races.rows', [])
        );
});

it('(2) gives a Retired run a different empty state, and it is not an invitation to decide a turn', function (): void {
    $run = careerResultRun(['status' => RunStatus::Retired], turns: 1);

    $this->get(route('runs.result', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('empty.reason', 'retired')
            ->where('empty.message', fn (string $message): bool => str_contains($message, 'retired'))
            // The two absences are distinguishable without reading prose: a page that reused the
            // Active reason would fail here.
            ->where('empty.message', fn (string $message): bool => ! str_contains($message, 'still running'))
        );
});

it('(4) reports no deficit at all when the run has no build target', function (): void {
    $run = careerResultRun([], turns: 1);

    $entry = TurnEntry::query()->where('training_run_id', $run->id)->first();

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            // The failure this guards is `target ?? 0`: it would make every deficit equal the
            // stat's own value. Naming both fields pins the honest shape instead.
            ->where('build.stats.0.target', null)
            ->where('build.stats.0.deficit', null)
            ->where('build.stats.0.deficit_title', fn (string $title): bool => str_contains($title, 'No build target'))
            ->where('build.stats.0.current', $entry->speed)
        );
});

it('(4) computes the deficit against the entered target, matching the advisor', function (): void {
    $run = careerResultRun([], turns: 0);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
    ]);

    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 1200, 'Stamina' => 500, 'Power' => 400, 'Guts' => 300, 'Wit' => 600],
        'skill_priorities' => [],
    ])->toArray()]);

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            // Speed: 1200 - 600 = 600 short. Stamina: met, so zero. Power and Guts: over target,
            // clamped to zero rather than negative. Wit: 600 - 500 = 100.
            ->where('build.stats.0.key', 'Speed')
            ->where('build.stats.0.target', 1200)
            ->where('build.stats.0.deficit', 600)
            ->where('build.stats.1.key', 'Stamina')
            ->where('build.stats.1.deficit', 0)
            ->where('build.stats.2.deficit', 0)
            ->where('build.stats.3.deficit', 0)
            ->where('build.stats.4.key', 'Wit')
            ->where('build.stats.4.deficit', 100)
        );
});

it('(1) reports four race counts, so an unrecorded finish is never counted as a loss', function (): void {
    $run = careerResultRun([], turns: 1);

    $turn = TurnEntry::query()->where('training_run_id', $run->id)->first();

    $g1 = RaceCatalogSlot::factory()->create(['tier' => 'G1', 'title' => 'Hopeful Stakes']);
    $g2 = RaceCatalogSlot::factory()->create(['tier' => 'G2', 'title' => 'Oka Sho']);

    // A win, and a G1 win.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turn->id,
        'race_catalog_slot_id' => $g1->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    // Ran, finished below first.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turn->id,
        'race_catalog_slot_id' => $g2->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
    ]);

    // Ran, and the finish was never recorded. This is the row the four-count rule exists for: a
    // two-count implementation folds it into "losses" and asserts a placing nobody entered.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turn->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => null,
    ]);

    // Declined before it was run: not a race the career ran, so no count reads it.
    RaceEntry::factory()->skipped()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turn->id,
    ]);

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('races.counts.completed', 3)
            ->where('races.counts.wins', 1)
            ->where('races.counts.below_first', 1)
            ->where('races.counts.placement_unrecorded', 1)
            ->where('races.counts.g1_wins', 1)
            // The declared key set, so a future "win rate" or score has nowhere to arrive.
            ->where('races.counts', fn ($counts): bool => array_keys($counts instanceof Collection ? $counts->all() : (array) $counts) === ['completed', 'wins', 'below_first', 'placement_unrecorded', 'g1_wins'])
        );
});

it('(3) offers Save Veteran as a real door with its url, now that D16 has landed', function (): void {
    $run = careerResultRun([], turns: 1);

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('save_veteran.available', true)
            ->where('save_veteran.url', route('runs.veteran', $run))
            ->where('save_veteran.reason', fn (string $reason): bool => ! str_contains($reason, 'has not landed'))
            // The key set is pinned so a score or a recommendation cannot arrive here without a test naming
            // it. `url` was added when the screen went from a named absence to a door; the held figures stay
            // on `Career/SaveVeteran.vue`, not on this payload.
            ->where('save_veteran', fn ($section): bool => array_keys($section instanceof Collection ? $section->all() : (array) $section) === ['available', 'reason', 'url'])
        );
});

it('names the door as not open yet for a career that has not finished', function (): void {
    $run = careerResultRun(['status' => RunStatus::Active], turns: 1);

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('save_veteran.available', false)
            ->where('save_veteran.reason', fn (string $reason): bool => str_contains($reason, 'Active')));
});

it('carries the ruleset as a named absence and lists the learned skills', function (): void {
    $run = careerResultRun([], turns: 1);

    $learned = Skill::factory()->create(['name' => 'Nihon Ichi no Uma']);
    $notLearned = Skill::factory()->create(['name' => 'Uma Stan']);

    $run->setSkillStatus($learned, SkillAcquisition::Acquired, 12);
    $run->setSkillStatus($notLearned, SkillAcquisition::Suggested);

    $this->get(route('runs.result', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('ruleset.value', null)
            ->where('ruleset.title', fn (string $title): bool => str_contains($title, 'ruleset'))
            ->has('build.skills.learned', 1)
            ->where('build.skills.learned.0.name', 'Nihon Ichi no Uma')
            ->where('build.skills.learned.0.turn_acquired', 12)
            ->where('build.skills.not_learned', 1)
        );
});
