<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Grade Point meter (D-232, gate G-34), after the Inertia port (ADR-0020 §1).
 *
 * D-232's hard part is not the bar, it is the denominator. There are four objectives
 * and surplus never carries forward, so a running total would imply a banking
 * strategy the game does not have. A meter showing 540/900 is a different claim from
 * 240/300, and only the second one is true. The tests assert the *current* objective
 * is the only denominator on the surface, and that a banked surplus is visibly
 * dropped rather than quietly carried.
 *
 * The meter is a Vue component now (`resources/js/components/GradePointMeter.vue`), so this file
 * asserts the two things a PHP assertion can still establish about it: the props the page ships
 * for each state, read off a real run carrying real finishes, and the decisions its own template
 * makes about them. Every figure below is priced by the scenario's own table rather than handed
 * to a renderer, so a state the port cannot reach fails as a payload instead of passing as a
 * string. Rendered copy is browser evidence in `tests/browser/run-detail.spec.ts`, which reaches
 * the Trackblazer meter and its withheld states.
 *
 * One case is gone, with the reason. "Refuses a scenario it has no descriptor for" was a
 * render-time throw on a `scenario` prop the Vue meter is never handed (G-33): the server resolves
 * the ladder through `gradeObjectives()` and an empty list is the off state, so the question cannot
 * be put to the component at all. The claim that replaces it is the stronger one asserted below —
 * the ladder the page ships is that same method, and it returns [] for a scenario that composes no
 * Grade Point panel.
 */

/**
 * A run in the period the Trainer reports as live, unless the case asks for another scenario.
 *
 * The period is entered and never derived (D-270), so any case that needs a denominator says
 * which deadline it is standing on rather than letting a date pick one.
 */
function meterRun(?int $period = null, string $scenario = 'trackblazer'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'current_objective_index' => $period,
    ]);
}

/**
 * One finish in one period, priced by the scenario's own table.
 *
 * The table prices a first place only, so a placement below 1 is how a case produces the
 * unpriceable state: the period withholds its total rather than returning a smaller one
 * (D-256, KI-10). Each call takes its own slot, because the price is keyed on the tier.
 */
function meterFinish(TrainingRun $run, int $period, string $grade = 'G1', int $placement = 1): RaceEntry
{
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'tier' => $grade,
    ]);

    return RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => $placement,
        'objective_index' => $period,
    ]);
}

/**
 * The meter's own template, which now owns the treatment and the copy.
 */
function meterTemplate(): string
{
    return (string) file_get_contents(base_path('resources/js/components/GradePointMeter.vue'));
}

/**
 * The run's own detail page. Every case reads its props through this one call, so a case can
 * never assert against a payload assembled some other way.
 */
function meterPage(TrainingRun $run): TestResponse
{
    return test()->get('/training-runs/'.$run->id)->assertOk();
}

it('measures progress against the current objective only', function (): void {
    // 240 into Classic's 300: a G1, a G2 and a G3 first place, all entered against period 3. The
    // other three deadlines sit on the ladder with their own sums beside them, so the only figure
    // the meter can draw a bar against is this one.
    $run = meterRun(period: 3);
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G2');
    meterFinish($run, 3, 'G3');

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The column is 1-based because ADR-0003 numbers the objectives that way; the payload
        // carries a 0-based position, because it is a list. Period 3 is position 2.
        ->where('gradeMeter.current', 2)
        ->where('gradeMeter.earned', 240)
        ->where('gradeMeter.objectives', fn (Collection $objectives): bool => $objectives->contains(
            fn (array $objective): bool => $objective['index'] === 3 && $objective['required'] === 300
        ))
        // No row is a running total. 60 + 300 + 300 is 660 and every objective summed is 900,
        // and neither is reachable as a period's own figure (D-232).
        ->where('gradeMeter.periods', fn (Collection $periods): bool => ! $periods->contains(
            fn (array $row): bool => in_array($row['earned'], [660, 900], true)
        )));

    // The bar's width is the live objective's own pair, and the ladder rows read their own period
    // out of the map rather than accumulating: nothing in the component sums the set.
    expect(meterTemplate())->toContain('const required = computed')
        ->not->toContain('reduce(');
});

it('states that surplus does not carry over', function (): void {
    // D-232's sentence is the claim itself, so it is asserted in the words the design record names
    // rather than paraphrased. The rendered sentence is browser evidence; what PHP can still prove is
    // that the component says it at all, and says the second half too — a bar that says "does not
    // carry" without saying what happens instead leaves the Trainer to guess.
    expect(meterTemplate())
        ->toContain('Surplus does not carry over.')
        ->toContain('is not banked toward the next one');
});

it('shows a completed objective as complete and does not add its points to the bar', function (): void {
    // Junior (60) and Classic (300) are both met; Senior stands at 120/300 from a G1 and a Pre-OP
    // win. A running total would read 480, which is the banking arithmetic D-232 forbids.
    $run = meterRun(period: 4);
    meterFinish($run, 2, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 4, 'G1');
    meterFinish($run, 4, 'Pre-OP');

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.current', 3)
        ->where('gradeMeter.earned', 120)
        // The figures are the price table's, not the deadlines': a G1 win is 100 whether the
        // deadline it counts toward asks for 60 or 300. Junior is met at 100 of 60.
        ->where('gradeMeter.periods', fn (Collection $periods): bool => [
            $periods[1]['index'] => $periods[1]['earned'],
            $periods[2]['index'] => $periods[2]['earned'],
            $periods[3]['index'] => $periods[3]['earned'],
        ] === [2 => 100, 3 => 300, 4 => 120])
        // The set of periods is never added up, so 480 is not a figure the page can show.
        ->where('gradeMeter.periods', fn (Collection $periods): bool => ! $periods->contains(
            fn (array $row): bool => $row['earned'] === 480
        )));

    // "Complete" is a word on the row, and the live deadline is marked rather than merely present,
    // so a screen reader is told which of the four the bar belongs to.
    expect(meterTemplate())->toContain('Complete')
        ->toContain('aria-current');
});

it('draws every progress fill dark enough to see on its own track', function (): void {
    // Found by the manual browser pass on 2026-09-28, not by a rule anyone had written down.
    // `bg-green` (#7FCC09) over the light `sunken` track (#E7E7EC) measures 1.62:1 — WCAG 1.4.11
    // wants 3:1 for a graphic you need in order to read the value, and the bar IS the value here.
    // The same pair in dark measured 8.84:1, so the defect only showed on the theme this app now
    // treats as the base. `bg-green-deep` is the theme-paired step of the same hue the "Complete"
    // word already uses: 4.20:1 on light sunken, 11.74:1 on dark sunken.
    //
    // The stat band draws the same bar over the same track, so the correction is asserted across
    // both rather than left fixed in one (D-286). The guard reads the sources because a bar fill is
    // a treatment, not a value, and both owners are Vue components after the port (ADR-0020 §1).
    foreach (['GradePointMeter.vue', 'StatBand.vue'] as $component) {
        $source = (string) file_get_contents(base_path('resources/js/components/'.$component));

        // `bg-green` must not appear as a whole class. A plain \b would break on the hyphen in
        // `bg-green-deep` and flag the fixed code, hence the lookahead.
        expect($source)
            ->not->toMatch('/class="[^"]*\bbg-green(?![-\w])[^"]*"[^>]*:style=/')
            ->toMatch('/bg-green-deep/');
    }
});

it('reports an over-achievement as spent rather than as progress past the cap', function (): void {
    // 420 against a 300 objective. Points past it are real points the Trainer earned, and the meter
    // has to say they go nowhere rather than draw a bar at 140% or quietly top out at full.
    $run = meterRun(period: 3);
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'Pre-OP');

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.earned', 420)
        ->where('gradeMeter.objectives', fn (Collection $objectives): bool => $objectives->contains(
            fn (array $objective): bool => $objective['index'] === 3 && $objective['required'] === 300
        )));

    // Three halves of the claim, because any two can be satisfied by a bar that simply tops out:
    // the surplus is named, the cap is still the denominator, and no percentage is invented.
    expect(meterTemplate())
        ->toContain('over the objective')
        ->toContain('past the objective, and not banked')
        ->not->toContain('140%');
});

it('shows the objective ladder so the next deadline is legible', function (): void {
    $run = meterRun(period: 2);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.current', 1)
        ->where('gradeMeter.objectives', fn (Collection $objectives): bool => $objectives->pluck('name')->all() === [
            'Debut race', 'End of Junior Year', 'End of Classic Year', 'End of Senior Year',
        ]));

    // The ladder is a list with its own name, the current row is marked rather than merely present,
    // and each row prints its own required figure so the ladder is legible without the bar.
    expect(meterTemplate())
        ->toContain('aria-label="Grade Point objectives"')
        ->toContain('aria-current');
});

it('claims no figure when the run has entered none', function (): void {
    // The deadlines are the scenario's and stay printed — they are the reason to open the panel. The
    // progress figure is the run's, and there isn't one, so a zero would be a claim about a trainee
    // that nobody entered (D-220).
    $run = meterRun(period: 3);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('gradeMeter.earned')
        ->where('gradeMeter.unpricedCount', 0)
        ->where('gradeMeter.objectives', fn (Collection $objectives): bool => $objectives->contains(
            fn (array $objective): bool => $objective['index'] === 3 && $objective['required'] === 300
        )));

    // The ratio line lives inside the branch guarded on `logged`, so a withheld figure has nowhere
    // to print a number and cannot fall back to a zero standing in for one.
    expect(meterTemplate())
        ->toContain('not yet recorded')
        ->toContain('v-if="logged"');
});

it('withholds the denominator too when the Trainer has named no period', function (): void {
    // The Blade meter defaulted to the first deadline, so a run with nothing logged read as though
    // it were standing on objective one: a consequence of the empty log presented as an assumption
    // about the year. The port's off state is a null position, which selects the branch that names
    // the absence instead.
    $run = meterRun();

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('gradeMeter.current')
        ->whereNull('gradeMeter.earned')
        ->where('gradeMeter.objectives', fn (Collection $objectives): bool => $objectives->isNotEmpty()));

    expect(meterTemplate())->toContain('no period reported');

    // The schema half of this claim — that the column stays null until a Trainer enters it — is
    // GradePointPeriodTest's, and is not restated here.
});

it('renders nothing at all for a scenario with no Grade Point objectives', function (string $scenario): void {
    // D-221 / D-241: the meter substitutes for the calendar only where there are objectives. On a
    // URA or Unity Cup run it must be absent, not a disabled shell. Absence is decided by the server
    // now — an empty ladder — and the component's only job left is to honour it.
    $run = meterRun(scenario: $scenario);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.objectives', [])
        ->whereNull('gradeMeter.earned'));

    expect(meterTemplate())->toContain('v-if="objectives.length > 0"');
})->with(['ura_finale', 'unity_cup', 'our_grand_concert']);

it('shows the two countdowns the economy is actually driven by', function (): void {
    // D-232 calls the rotation countdown the shop's primary number, not the balance. It is the
    // scenario's own config value, so the page ships it and the component prints it.
    $run = meterRun(period: 2);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.rotationTurns', (int) config('scenarios.scenarios.trackblazer.shop.rotation_turns')));

    expect(meterTemplate())->toContain('Rotation resets in');
});

it('does not invent a coin balance or a turn count', function (): void {
    $run = meterRun(period: 2);

    // The rotation is sourced and prints; the balance is run state and does not, so it is named as
    // missing rather than shown as 0 — a zero balance would read as "you are broke" when the truth
    // is "nothing entered yet". The payload is where that claim is proven: there is no field to
    // print a balance from, so a component reaching for one would be reaching for nothing.
    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter', fn (Collection $meter): bool => ! $meter->has('shop_coins')
            && ! $meter->has('balance')
            && ! $meter->has('coins')));

    expect(meterTemplate())
        ->toContain('Shop Coins: not yet recorded')
        ->not->toMatch('/Shop Coins:\s*\{\{/');
});

it('names the reason when results are logged but cannot be priced', function (): void {
    // R18's middle state. A run with a G1 win plus a free-form race read "no Grade Points are
    // entered for this run" — a sentence blaming the Trainer for races they did enter (KI-12). The
    // total is still withheld; what changes is that the panel says why.
    $run = meterRun(period: 2);
    meterFinish($run, 2, 'G1', 2);
    meterFinish($run, 2, 'G2', 3);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('gradeMeter.earned')
        ->where('gradeMeter.unpricedCount', 2));

    expect(meterTemplate())
        ->toContain('not yet totalled')
        ->toContain('no published Grade Point value');
});

it('still says nothing was recorded when nothing was recorded', function (): void {
    // The first state must survive the split. A run with no races at all is a different fact from a
    // run whose races cannot be priced, and the two used to share one sentence true only of the
    // first.
    $run = meterRun(period: 2);

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('gradeMeter.earned')
        ->where('gradeMeter.unpricedCount', 0));

    // Scoped to the figure, deliberately: the footer's Shop Coins line legitimately still reads
    // "not yet recorded" here, so a whole-document assertion would be testing the wrong widget.
    expect(meterTemplate())->toContain('no races are');
});

it('keeps the figure state when every logged result can be priced', function (): void {
    $run = meterRun(period: 3);
    meterFinish($run, 3, 'G1');
    meterFinish($run, 3, 'G2');
    meterFinish($run, 3, 'G3');

    meterPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.earned', 240)
        ->where('gradeMeter.unpricedCount', 0));

    // The figure line is the one guarded on `logged`, and it reads the same two numbers the bar
    // does, so a figure the bar draws is a figure the text states.
    expect(meterTemplate())
        ->toContain('fmt(earnedVal as number) }} /')
        ->not->toContain('logged results');
});

it('contains no scenario name anywhere in the component', function (): void {
    // D-240 / G-33: adding a fifth scenario must be one config entry and no component edit. The
    // prop carries the resolved ladder rather than a key, so a slug cannot appear even by mistake.
    $code = (string) preg_replace(
        ['#<!--.*?-->#s', '#/\*.*?\*/#s', '#^\s*//.*$#m'],
        '',
        meterTemplate(),
    );

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});
