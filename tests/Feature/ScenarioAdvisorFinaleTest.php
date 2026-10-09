<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The advisor's finale line (Slice 21). Five cases, all reachable on the real grid: the finale block
 * opens one turn after the 72-turn career grid ends, so a run that has logged every turn has passed
 * the block rather than lost its position.
 *
 * N is `TrainerAdvisor::FINALE_TURNS_AHEAD`, five, and it is a display choice of this tool, not a
 * rule of the game; the reason is in the method's docblock. Nothing here advises on the concert: the
 * card's own action is asserted unchanged, because a status line that displaced the recommendation
 * would be the second opinion `ADR-0020` §3 keeps off this screen.
 */
function advisorFinaleRun(string $scenario, int $turn): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn, 'energy' => 70]);

    return $run;
}

function advisorFinaleSlot(string $scenarioKey): void
{
    RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Catalogue finale row',
        'is_mandatory' => true,
    ]);
}

it('says nothing about a finale the scenario does not have', function (): void {
    $run = advisorFinaleRun('ura_finale', 71);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('advisor.finale', null));
});

it('stays quiet while the finale is further away than the display window', function (): void {
    $run = advisorFinaleRun('our_grand_concert', 60);
    advisorFinaleSlot('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('advisor.finale', null));
});

it('names the finale and counts the turns while it is close', function (): void {
    $run = advisorFinaleRun('our_grand_concert', 68);
    advisorFinaleSlot('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.finale.label', 'Our Grand Concert')
            ->where('advisor.finale.state', 'upcoming')
            ->where('advisor.finale.turns_away', 4)
            // The recommendation itself is untouched by the status line: this run sets no build
            // target, so the engine still declines to name an action and says why, exactly as it did
            // before the finale existed here.
            ->where('advisor.action', null)
            ->has('advisor.reasons', 1)
        );
});

it('reports the finale as the next turn on the last turn of the grid', function (): void {
    $run = advisorFinaleRun('our_grand_concert', 71);
    advisorFinaleSlot('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.finale.state', 'next')
            ->where('advisor.finale.turns_away', 1)
        );
});

it('reports the finale as passed once the run has gone past the block', function (): void {
    $run = advisorFinaleRun('our_grand_concert', 72);
    advisorFinaleSlot('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.finale.state', 'passed')
            ->where('advisor.finale.turns_away', null)
        );
});
