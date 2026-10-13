<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Models\CharacterCard;
use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The detail page's server contract: the profile block above, the per-form content below, and the
 * form the page opens on.
 *
 * The page is the Catalog/Show Inertia component (ADR-0020 §1), so this file asserts the resolved
 * props and the behaviours the request named — `?form=` selection, its fallback, and the
 * unconfirmed opt-in. The rendered words (the six profile labels, the eight-section binding order,
 * the tab strip and the "no chrome on one form" threshold, the Unique pill, and every absence
 * sentence) are asserted against the live DOM in tests/browser/catalog-detail.spec.ts.
 */

/**
 * A trainee with the given number of forms, all released on Global.
 */
function detailTrainee(int $forms): Umamusume
{
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'slug' => 'special-week',
        'release_status' => ReleaseStatus::GlobalReleased,
    ]);

    foreach (range(1, $forms) as $index) {
        CharacterCard::factory()->create([
            'umamusume_id' => $umamusume->id,
            'card_id' => 900000 + $index,
            'title' => "[Global] Form {$index}",
            'rarity' => CardRarity::ThreeStar,
            'is_debut_form' => $index === 1,
            'global_release_date' => '2026-0'.$index.'-01',
        ]);
    }

    return $umamusume->fresh();
}

it('carries every form with its title when a trainee has more than one', function (): void {
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->has('cards', 2)
            ->where('cards.0.title', '[Global] Form 1')
            ->where('cards.1.title', '[Global] Form 2')
            // The page opens on the first form in cardScope() order.
            ->where('activeCardId', $umamusume->cards->first()->id));
});

it('carries the single form with no choice to make', function (): void {
    // The threshold. One form is not a choice, and a one-option control is chrome the Trainer
    // has to read past to reach the same content. The content is still there: no chrome, not
    // less page (the strip's absence is asserted in tests/browser/catalog-detail.spec.ts).
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards', 1)
            ->where('cards.0.title', '[Global] Form 1')
            ->where('activeCardId', $umamusume->cards->first()->id));
});

it('orders the forms debut first, then by Global release date', function (): void {
    $umamusume = detailTrainee(3);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards', 3)
            ->where('cards.0.title', '[Global] Form 1')
            ->where('cards.0.is_debut_form', true)
            ->where('cards.1.title', '[Global] Form 2')
            ->where('cards.2.title', '[Global] Form 3'));
});

it('opens the first form by default and honours ?form=', function (): void {
    $umamusume = detailTrainee(3);
    $cards = $umamusume->cards;

    // Default: the first card in the controller's scope order is the active form.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCardId', $cards->first()->id));

    // Addressable: a link into the second form survives a reload.
    $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'form' => $cards[1]->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCardId', $cards[1]->id));
});

it('falls back to the first form for a ?form= that names no card on this page', function (): void {
    // A stale shared link should show the trainee, not a 404.
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'form' => 999999]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCardId', $umamusume->cards->first()->id));
});

it('carries the unconfirmed opt-in so a form link does not close it', function (): void {
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'show_unconfirmed' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('showUnconfirmed', true));
});

it('resolves the six profile fields for a complete profile', function (): void {
    $umamusume = detailTrainee(2);
    UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.japanese_name', 'スペシャルウィーク')
            ->where('trainee.profile.va_ja', '和氣あず未')
            ->where('trainee.profile.va_en', 'Azumi Waki')
            ->where('trainee.profile.birthday.display', 'May 2, 1995')
            ->where('trainee.profile.height', 158)
            ->where('trainee.profile.three_sizes.b', 81)
            ->where('trainee.profile.three_sizes.h', 81)
            ->where('trainee.profile.three_sizes.w', 56));
});

it('leaves an unpublished value null rather than a placeholder', function (): void {
    // D-220. Measured: va_en and three_sizes are each absent on 10 of the 135 roster rows, so
    // this is the normal path for a Trainer, not an edge case. The words that say so are in
    // tests/browser/catalog-detail.spec.ts.
    $umamusume = detailTrainee(1);
    UmamusumeProfile::factory()->partial()->create(['umamusume_id' => $umamusume->id]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.profile.va_en', null)
            ->where('trainee.profile.three_sizes', null));
});

it('names the missing birth year instead of inventing one', function (): void {
    // `birth_year` is the only birthday part the source omits (17 of 163 rows). A January 1st
    // would be a date the source never stated, so the year gap is a null `iso` carrying words.
    $umamusume = detailTrainee(1);
    UmamusumeProfile::factory()->withoutBirthYear()->create([
        'umamusume_id' => $umamusume->id,
        'birth_month' => 3,
        'birth_day' => 20,
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.profile.birthday.iso', null)
            ->where('trainee.profile.birthday.display', 'Mar 20 · year not published'));
});

it('resolves no profile block when no profile has been written', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainee.profile', null));
});

it('resolves the ten aptitude letters', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->update([
        'aptitude_turf' => 'A',
        'aptitude_dirt' => 'B',
        'aptitude_sprint' => 'C',
        'aptitude_mile' => 'D',
        'aptitude_medium' => 'E',
        'aptitude_long' => 'F',
        'aptitude_front_runner' => 'G',
        'aptitude_pace_chaser' => 'A',
        'aptitude_late_surger' => 'B',
        'aptitude_end_closer' => 'C',
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // All ten letters, each read from the trainee rather than the card.
            ->where('trainee.aptitudes.turf', 'A')
            ->where('trainee.aptitudes.dirt', 'B')
            ->where('trainee.aptitudes.sprint', 'C')
            ->where('trainee.aptitudes.mile', 'D')
            ->where('trainee.aptitudes.medium', 'E')
            ->where('trainee.aptitudes.long', 'F')
            ->where('trainee.aptitudes.front_runner', 'G')
            ->where('trainee.aptitudes.pace_chaser', 'A')
            ->where('trainee.aptitudes.late_surger', 'B')
            ->where('trainee.aptitudes.end_closer', 'C'));
});

it('resolves no aptitude grid rather than a half grid', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainee.aptitudes', null));
});

it('names this form\'s own source and read date (D-33)', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cards.0.source_url', 'https://gametora.test/character-cards.json')
            ->where('cards.0.fetched_at_display', now()->timezone(config('uma.display_timezone'))->format('M j, Y')));
});

it('resolves her unique and innate skills', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->cards()->first()->update([
        'skills_unique' => [900001],
        'skills_innate' => [900002, 900003],
    ]);

    // Fixture names are obviously fake: a plausible-sounding invented skill name is
    // indistinguishable from a real one at a glance, and this repo does not store unsourced rows.
    Skill::factory()->create(['export_id' => 900001, 'name' => 'Test Unique Skill']);
    Skill::factory()->create(['export_id' => 900002, 'name' => 'Test Innate Skill A']);
    Skill::factory()->create(['export_id' => 900003, 'name' => 'Test Innate Skill B']);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skillLists.0.label', 'Her unique skills')
            ->where('skillLists.0.skills.0.name', 'Test Unique Skill')
            ->where('skillLists.1.label', 'Her innate skills')
            ->where('skillLists.1.skills.0.name', 'Test Innate Skill A')
            ->where('skillLists.1.skills.1.name', 'Test Innate Skill B'));
});

it('resolves every skill list as empty when the form carries none', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skillLists.0.has_ids', false)
            ->where('skillLists.1.has_ids', false)
            ->where('skillLists.2.has_ids', false)
            ->where('skillLists.3.has_ids', false));
});

it('lists her runs with status, scenario and turn count', function (): void {
    $umamusume = detailTrainee(1);
    TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs', 1)
            ->where('runs.0.status_label', RunStatus::Active->label())
            ->where('runs.0.scenario_label', 'URA Finale')
            ->where('runs.0.turn_count', 0));
});

it('resolves no runs when she has none', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('runs', 0));
});

it('flags a Japan-only trainee for the not-yet-on-Global notice', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->update(['release_status' => ReleaseStatus::JapanOnly]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.is_japan_only', true)
            ->where('trainee.release_status_label', 'Japan only'));
});

it('resolves the release date the profile block prints, Global first', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->update([
        'jp_debut_date' => '2021-03-01',
        'global_debut_date' => '2025-06-26',
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.release_date.display', 'Jun 26, 2025')
            ->where('trainee.release_date.is_global', true));
});

it('still resolves the Japanese name before the profile fetch has run', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'slug' => 'special-week',
        'release_status' => ReleaseStatus::GlobalReleased,
    ]);

    expect($umamusume->fresh()->profile)->toBeNull();

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainee.japanese_name', 'スペシャルウィーク')
            ->where('trainee.profile', null));
});

it('prefers the profile document and falls back to the trainee column for the Japanese name', function (): void {
    $umamusume = detailTrainee(1);
    $profile = UmamusumeProfile::factory()->create([
        'umamusume_id' => $umamusume->id,
        'name_ja' => 'スペシャルウィーク・改',
    ]);

    expect($umamusume->fresh()->japaneseName())->toBe('スペシャルウィーク・改');

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainee.japanese_name', 'スペシャルウィーク・改'));

    $profile->update(['name_ja' => null]);

    expect($umamusume->fresh()->japaneseName())->toBe('スペシャルウィーク');
});

/*
 * ADR-0026's widest-reach half (owner decision 3): the trainee's own objectives, read on the catalog
 * detail page, where no run is in progress to derive anything from. `CockpitController::traineeGoalRows()`
 * composes the same rows for a career that is running; the pair of tests below pins that the two agree
 * row-for-row and that this page claims nothing it cannot source.
 */

/**
 * A trainee with a three-rung ladder spanning two career years.
 */
function ladderTrainee(): Umamusume
{
    $umamusume = detailTrainee(1);

    $debut = RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 1, 'half' => 'Late', 'title' => 'Make Debut',
    ]);
    $derby = RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 2, 'month' => 4, 'half' => 'Late', 'title' => 'Tokyo Yushun (Japanese Derby)',
    ]);

    TraineeGoal::factory()->participate()->create([
        'umamusume_id' => $umamusume->id,
        'race_catalog_slot_id' => $debut->id,
        'career_year' => 1, 'turn' => 12, 'source_order' => 1,
    ]);
    TraineeGoal::factory()->fan(5000, 37)->create([
        'umamusume_id' => $umamusume->id,
        'source_order' => 2,
    ]);
    TraineeGoal::factory()->create([
        'umamusume_id' => $umamusume->id,
        'race_catalog_slot_id' => $derby->id,
        'goal_type' => TraineeGoalType::Placement,
        'required_placement' => 5,
        'career_year' => 2, 'turn' => 34, 'source_order' => 3,
    ]);

    return $umamusume;
}

it('lists her own ladder in the order the source publishes it', function (): void {
    $umamusume = ladderTrainee();

    $rows = $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->inertiaProps('traineeGoals.rows');

    // Ordered by the career and then by the deadline turn, not by insertion: Junior first, then the
    // turn-34 Derby ahead of the turn-37 fan gate even though the source orders them the other way.
    expect($rows)->toHaveCount(3)
        ->and($rows[0]['label'])->toBe('Participate in Make Debut')
        ->and($rows[0]['year_label'])->toBe('Junior')
        ->and($rows[0]['turn'])->toBe(12)
        ->and($rows[1]['label'])->toBe('Place top 5 in Tokyo Yushun (Japanese Derby)')
        ->and($rows[1]['year_label'])->toBe('Classic')
        ->and($rows[1]['turn'])->toBe(10)
        ->and($rows[2]['label'])->toBe('Earn 5000 fans')
        // 37 counted from the start of the career is turn 13 of its own year, which is the clock every
        // other turn on this page prints.
        ->and($rows[2]['year_label'])->toBe('Classic')
        ->and($rows[2]['turn'])->toBe(13);
});

it('claims no progress it has no run to read', function (): void {
    $umamusume = ladderTrainee();

    $ladder = $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->inertiaProps('traineeGoals');

    // `state`, `race_url` and the cleared count are the run-scoped half of the Cockpit's row contract.
    // They are null here because this screen has no run, and a due-now mark or a `Goals Achieved` figure
    // without one would be an invented reading (PRD §6.11, and the same rule plan §5.5 sets for
    // provenance: a badge qualifies a value that exists).
    expect($ladder['rows'])->toHaveCount(3)
        ->and(array_column($ladder['rows'], 'state'))->toBe([null, null, null])
        ->and(array_column($ladder['rows'], 'race_url'))->toBe([null, null, null])
        ->and(array_column($ladder['rows'], 'absence'))->toBe([null, null, null])
        ->and($ladder['summary'])->toBeNull()
        ->and($ladder['empty_note'])->toBeNull();
});

it('prints the same rows the Cockpit prints for the same trainee', function (): void {
    $umamusume = ladderTrainee();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
    ]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 12, 'fans' => 100]);

    $catalog = $this->get(route('catalog.show', $umamusume->slug))->assertOk()->inertiaProps('traineeGoals.rows');

    /** @var list<array<string, mixed>> $cockpit */
    $cockpit = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.objectives')[0]['rows'];

    // The two surfaces read the same model methods, so a drift between them is a bug in one of the
    // readers rather than a difference the page is allowed to have.
    expect(array_column($catalog, 'label'))->toBe(array_column($cockpit, 'label'))
        ->and(array_column($catalog, 'year_label'))->toBe(array_column($cockpit, 'year_label'))
        ->and(array_column($catalog, 'turn'))->toBe(array_column($cockpit, 'turn'))
        ->and(array_column($catalog, 'absence'))->toBe(array_column($cockpit, 'absence'));
});

it('names the unread requirement in the same sentence the Cockpit uses', function (): void {
    $umamusume = detailTrainee(1);
    TraineeGoal::factory()->undecoded()->create([
        'umamusume_id' => $umamusume->id,
        'source_cond_type' => 2,
        'source_cond_value' => 7,
        'source_cond_value_2' => 0,
    ]);

    $rows = $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->inertiaProps('traineeGoals.rows');

    expect($rows[0]['absence'])->toBe('The source states a requirement for this objective that this tool'
        .' has not decoded. Its own figures are condition type 2, value 7, kept raw on the row.');
});

it('says no ladder is loaded when this database holds none for anybody', function (): void {
    // The empty branch, and the honest name for it. Measured on a seeded scratch database on 2026-10-14: all
    // 67 promoted trainees carry rows, so no seeded database reaches this branch, exactly like the "Not yet
    // released on Global" notice `tests/browser/catalog-detail.spec.ts:9-16` records. Because the state here
    // is an unseeded table rather than a trainee the source skips, the sentence may only speak of the table
    // (`TraineeGoal::ladderAbsenceReason()`, asserted in `TraineeGoalLadderStateTest`).
    $umamusume = detailTrainee(1);

    $ladder = $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->inertiaProps('traineeGoals');

    expect($ladder['rows'])->toBe([])
        ->and($ladder['empty_note'])->toBe('No objective ladder has been loaded into this database,'
            .' so nothing is listed here.');
});

it('composes the ladder fresh after a promotion, in two queries whatever its length', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Rice Shower',
        'slug' => 'rice-shower',
        'release_status' => ReleaseStatus::GlobalReleased,
        'external_ref' => 'gametora:char:1030',
    ]);

    Cache::forget('catalog:version');
    $this->get(route('catalog.show', $umamusume->slug))->assertOk();

    // The real promotion path rather than a factory row, because the store action is what writes a
    // ladder. `show()` holds no copy of it, so the next read composes the stored rows; a later change
    // that caches this page without keying on `catalog:version` fails here (`CatalogController::show()`).
    app(PipelineRunner::class)->run(
        'gametora-ura-objectives',
        config('uma.sources.gametora-ura-objectives'),
        (string) file_get_contents(database_path('seeders/data/ura-objectives.ead4bbfe.json')),
        'snapshots/ura-objectives.ead4bbfe.json',
    );

    DB::flushQueryLog();
    DB::enableQueryLog();

    $props = $this->get(route('catalog.show', $umamusume->slug))->assertOk()->inertiaProps('traineeGoals');

    $expected = TraineeGoal::query()
        ->where('umamusume_id', $umamusume->id)
        ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
        ->count();

    // One query for the ladder and one batched for the calendar titles it names, for a 1,701-objective
    // document: a per-row lookup would show up as a count well above two.
    $ladderQueries = array_filter(
        array_column(DB::getQueryLog(), 'query'),
        static fn (string $sql): bool => str_contains($sql, 'trainee_goals'),
    );

    expect($props['rows'])->toHaveCount($expected)
        ->and($props['rows'])->not->toBe([])
        ->and($expected)->toBe(8)
        ->and($ladderQueries)->toHaveCount(2);
});
