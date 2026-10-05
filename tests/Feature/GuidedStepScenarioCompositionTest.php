<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Screen B for the baseline scenario (owner directive: build URA first, defer the
 * Trackblazer and Unity Cup variations until the baseline is verified).
 *
 * The baseline is not "the version with things removed" — URA Finale is defined by
 * absence (config/scenarios.php:74, "Defined by absence: no team system, no shop,
 * no scenario currency"). So the assertions that matter here are negative: the URA
 * turn must not name a shop, a team race, Grade Points or Shop Coins, because a step
 * or a caption mentioning them would state a mechanic the run has no source for.
 *
 * Since the Inertia port (ADR-0020 §1) the rail is composed entirely on the server:
 * `rail.def` is the resolved scenario config, `rail.choices` are the discipline
 * buttons, and `rail.current` is the one stage the response is actually in. The
 * component branches on the def's flags and owns only the presentation, so the
 * negative assertions below are stronger than they were: a mechanic URA does not
 * have cannot be printed because the payload that would carry it is not built.
 */

/**
 * A URA run with one logged turn, so the rail has an Energy figure and a turn to name.
 */
function uraRailRun(?int $energy = 78): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => $energy ?? 78,
        'fans' => 41250,
    ]);

    return $run->fresh();
}

/**
 * The rail component's source, which owns the step labels and the treatment.
 */
function guidedStepSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/GuidedStep.vue'));
}

/**
 * The step labels the component prints, read out of its own map.
 *
 * @return array<string, string>
 */
function guidedStepLabels(): array
{
    preg_match('/const STEP_LABELS: Record<string, string> = \{(.*?)\n\};/s', guidedStepSource(), $block);
    preg_match_all("/^\s{4}(\w+): '([^']*)',/m", $block[1] ?? '', $rows, PREG_SET_ORDER);

    $labels = [];

    foreach ($rows as $row) {
        $labels[$row[1]] = $row[2];
    }

    return $labels;
}

it('walks the baseline turn flow in config order and adds no step', function (): void {
    $run = uraRailRun();

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // Three steps in the matrix's own order, and the rail says which one it is on rather than
        // counting for itself: a plain GET is the first stage, never a step this tool invented.
        ->where('rail.def.steps', ['training', 'outcome', 'skill'])
        ->where('rail.current', 'training')
        ->where('rail.previewed', false));

    $labels = guidedStepLabels();

    // Unity Cup's facility step and Trackblazer's shop step belong to scenarios that own them.
    // They stay in the component's map because the other scenarios need them, but nothing in
    // URA's step list can reach them, so naming either here would be D-220 in the step rail.
    expect($labels)
        ->toHaveKey('facility')
        ->toHaveKey('shop')
        ->and(array_keys($labels))->not->toContain('team_race_step_ura');
});

it('states no mechanic the baseline scenario does not have', function (): void {
    $run = uraRailRun();

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(function (Assert $page): void {
        $page->component('Runs/Show')
            ->where('rail.declared', true)
            ->where('rail.def.label', (string) config('scenarios.scenarios.ura_finale.label'))
            ->where('rail.def.panels.shop', false)
            ->where('rail.def.panels.team_race', false);

        // The choice rows are the only other place the rail speaks, so they are swept for the same
        // six words rather than trusted because the labels look right.
        $page->where('rail.choices', function (Collection $choices): bool {
            $spoken = mb_strtolower($choices
                ->map(static fn (array $choice): string => ((string) $choice['label']).' '.((string) ($choice['detail'] ?? '')))
                ->implode(' '));

            foreach (['Team Race', 'Shop Coin', 'Grade Point', 'Spirit Burst', 'teammate', 'Team Rank'] as $word) {
                if (str_contains($spoken, mb_strtolower($word))) {
                    return false;
                }
            }

            return true;
        });
    });

    // The scenario's own config is where the absence lives, and the rail never adds a step the
    // config does not list.
    expect(config('scenarios.scenarios.ura_finale'))->not->toHaveKeys(['shop', 'team_race', 'shop_items']);
});

it('carries no fan gate on a choice, because the fan gate belongs to the calendar', function (): void {
    // The baseline calendar does gate a race on fans, and the gate is still stated: it is the
    // calendar cell's own `fans_needed` figure and the `Fan gate` word beside it, which
    // RaceCalendarTest and RaceSlotPanelComposerTest cover. What must not happen is the gate
    // migrating onto a discipline button, where it would read as a condition on training.
    $run = uraRailRun();

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // Seven choices: the five disciplines plus Rest and Recreation, and nothing else.
        ->where('rail.choices', fn (Collection $choices): bool => $choices->count() === 7)
        ->where('rail.choices', fn (Collection $choices): bool => $choices
            ->every(static fn (array $choice): bool => ! str_contains(mb_strtolower((string) ($choice['detail'] ?? '')), 'fans'))));

    // And no detail line carries a number at all: the rest figures are probabilities, and §2.2
    // keeps them out of application code.
    expect(guidedStepSource())->not->toContain('12,000');
});

it('colours increases orange and decreases blue', function (): void {
    // The client's pair is inverted from the web's green-up / red-down habit, so a
    // sign character is not enough: the class is the contract (research §3.2).
    expect(guidedStepSource())
        ->toContain(":class=\"delta.direction === 'down' ? 'text-down' : 'text-up'\"")
        // Gains are orange and losses blue; green is reserved for actions (§3.3).
        ->not->toContain('text-green');
});

it('places the Energy gauge immediately before the control that spends it', function (): void {
    // D-171 / G-30: a gauge carried only in the header is read once at the top of
    // the screen and forgotten before the button.
    $run = uraRailRun(78);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.energy', 78));

    $source = guidedStepSource();

    $gauge = strpos($source, ':aria-label="`Energy ${Number(energy)} of 100`"');
    $confirm = strpos($source, 'Confirm turn');

    expect($gauge)->toBeInt()->and($confirm)->toBeInt()->and($gauge)->toBeLessThan($confirm)
        ->and($source)->toContain('Energy {{ Number(energy) }}/100');
});

it('shows the unverified-label treatment rather than promoting a guess', function (): void {
    // The treatment is the component's, so it is read from the component; and nothing the server
    // sends today sets the flag, because no client string for the discipline buttons has been read.
    $run = uraRailRun();

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.choices', fn (Collection $choices): bool => $choices
            ->every(static fn (array $choice): bool => ($choice['unverified'] ?? false) === false)));

    expect(guidedStepSource())
        ->toContain('v-if="choice.unverified === true"')
        ->toContain('[Unverified]')
        ->toContain('title="Not confirmed as the Global client string"');
});

it('contains no scenario name anywhere in the component', function (): void {
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', guidedStepSource());

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});

it('never sees an unknown scenario, because the rail is handed a resolved config', function (): void {
    // The throw this file used to hold is gone with the port, and deliberately. The component no
    // longer takes a scenario name at all — it takes the resolved def — so there is no
    // unknown-scenario case left for it to refuse, and the server can always answer: a blank or
    // absent scenario falls back to the baseline key rather than being passed through as a lookup.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The baseline def is delivered whole, and the rail is told not to name it out loud.
        ->where('rail.def.steps', ['training', 'outcome', 'skill'])
        ->where('rail.declared', false)
        ->where('rail.def.label', (string) config('scenarios.scenarios.'.config('scenarios.baseline').'.label')));

    expect(guidedStepSource())->toContain('v-if="declared"');
});