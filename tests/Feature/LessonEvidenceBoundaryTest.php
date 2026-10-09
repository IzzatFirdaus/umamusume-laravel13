<?php

declare(strict_types=1);

use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
 * The Lesson evidence boundary (Grand Concert Slice 8).
 *
 * `docs/UMAMUSUME_REFERENCE.md` §2.9 says the `[Global]` launch notice resolved the Performance naming
 * rows and did NOT resolve the Lesson ones: "the two bonus layers (row 51, which the notice never
 * names), the Song titles and their language (row 49), the Lesson confirm and reserve button labels"
 * still wait on a client capture, and §2.8 names the Lesson list as one of the two screens a capture
 * would clear. `docs/scenarios/07` adds that every magnitude is unverified — no cost curve, no
 * per-level value — and that the printed Song cost figures cannot be attributed to a type at all.
 *
 * So there is no verified vocabulary to make an enum from, no verified partition into kinds, no
 * verified cost, and no verified effect. The honest output of this slice is therefore the boundary
 * below rather than a Lesson payload, and these tests exist so the boundary is a fact that fails when
 * it is crossed instead of a paragraph that is agreed with and forgotten.
 *
 * They are deliberately structural. A word-level search for "Lesson" would pass nothing: the Slice 3-6
 * refusals name the thing they refuse ("no source … states what a Lesson costs"), which is the
 * documentation doing its job.
 */
function lessonBoundaryRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

it('holds no Lesson domain type, because no Lesson vocabulary is verified enough to enumerate', function (): void {
    // A string-backed enum is only honest when its cases are the client's words. `PerformanceType`
    // qualifies on notice 905's sentence; Lessons do not, so inventing `LessonKind` would promote a
    // publisher's prose into a closed set the app then refuses anything outside of.
    expect(enum_exists('App\Enums\LessonKind'))->toBeFalse()
        ->and(enum_exists('App\Enums\LessonType'))->toBeFalse()
        ->and(class_exists('App\Models\TurnEvents\LessonPayload'))->toBeFalse()
        ->and(class_exists('App\Models\Lesson'))->toBeFalse();
});

it('holds no Lesson storage, because a Lesson has no verified field set to store', function (): void {
    expect(Schema::hasTable('lessons'))->toBeFalse()
        ->and(Schema::hasTable('lesson_observations'))->toBeFalse()
        ->and(Schema::hasColumn('turn_events', 'lesson'))->toBeFalse()
        ->and(Schema::hasColumn('turn_entries', 'lesson'))->toBeFalse();
});

it('exposes no Lesson reader, so nothing half-lands on the read side', function (): void {
    $run = lessonBoundaryRun();

    expect(method_exists($run, 'lessonObservations'))->toBeFalse()
        ->and(method_exists($run, 'lessons'))->toBeFalse()
        ->and(method_exists(TurnEvent::class, 'lessonPayload'))->toBeFalse();
});

it('offers no Lesson route or screen, because there is no truthful control to put on one', function (): void {
    $names = array_keys(Route::getRoutes()->getRoutesByName());
    $uris = array_map(static fn ($route): string => $route->uri(), Route::getRoutes()->getRoutes());

    $matches = static fn (array $haystack): array => array_values(array_filter(
        $haystack,
        static fn (string $needle): bool => str_contains(mb_strtolower($needle), 'lesson'),
    ));

    expect($matches($names))->toBe([])
        ->and($matches($uris))->toBe([]);
});

it('promotes no Lesson word into displayed vocabulary, where client copy is allowed to come from', function (): void {
    // `lang/en/uma.php` is the Global terms map: the file that says what the client prints. A word
    // reaching it is a claim that the client prints it, and the corpus says the Lesson list text is
    // precisely what has never been read.
    $vocabulary = require base_path('lang/en/uma.php');

    expect(collect($vocabulary)->flatten()->filter(
        static fn ($value): bool => is_string($value) && str_contains(mb_strtolower($value), 'lesson'),
    )->all())->toBe([]);
});

it('refuses to let a Lesson field ride in on the turn write, rather than half-supporting one', function (): void {
    $run = lessonBoundaryRun();

    // A speculative input that is silently ignored is worse than one that is refused in the open: it
    // looks like support. So this pins the actual behaviour — the key is not collected, nothing is
    // written under it, and no Lesson row appears beside the turn.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'confirm',
        'previewed' => '1',
        'choice' => 'training-Speed',
        'outcome' => 'Success',
        'lesson' => ['name' => 'Some Lesson', 'cost' => 21],
    ])->assertRedirect();

    expect($run->turnEntries()->count())->toBe(1)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0)
        ->and($run->fresh()->performanceObservations())->toBe([]);
});

it('keeps the Grand Concert capability flag scoped to what is actually verified', function (): void {
    $run = lessonBoundaryRun();

    expect($run->acceptsPerformanceObservations())->toBeTrue()
        // No sibling flag was invented for Lessons: the flag exists because Performance's vocabulary
        // is official copy, and there is no such reason for a Lesson surface.
        ->and(config('scenarios.scenarios.our_grand_concert.lesson_input'))->toBeNull()
        ->and(config('scenarios.scenarios.our_grand_concert.lessons'))->toBeNull()
        // The panel map is unchanged in both directions: Lessons did not become a panel, and the
        // scenario still composes none.
        ->and(config('scenarios.scenarios.our_grand_concert.panels'))->toBe([
            'race_calendar' => false,
            'team_race' => false,
            'grade_objectives' => false,
            'shop' => false,
            'epithet_routes' => false,
            'team_rank_ladder' => false,
        ]);
});

it('leaves the existing observation log able to hold a Lesson event without inventing a number', function (): void {
    $run = lessonBoundaryRun();

    // The canonical place already exists and already requires nothing computed: `deltas` is nullable
    // because an event may fire with no consequence to record, and `Scenario` is the catch-all type.
    // What is missing is verified vocabulary to structure it with, which is the boundary, not a gap in
    // the mechanism.
    $event = $run->turnEvents()->create([
        'turn' => 5,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'A Lesson the Trainer says they took',
    ]);

    $fresh = TurnEvent::query()->findOrFail($event->id);

    expect($fresh->deltas)->toBeNull()
        ->and($fresh->event_type)->toBe(TurnEventType::Scenario)
        // and it is not mistaken for one of the typed observations that do have a contract
        ->and($fresh->performancePayload())->toBeNull()
        ->and($fresh->purchasePayload())->toBeNull()
        ->and($run->fresh()->performanceObservations())->toBe([]);
});
