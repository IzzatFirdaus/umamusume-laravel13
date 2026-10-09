<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The finale's absence sentence (Slice 19).
 *
 * `CockpitController` used to answer an absent config `finale` key with "This scenario declares no
 * finale structure, so none is shown." for every scenario. That is false for the four `[Global]`
 * scenarios: each carries exactly one scenario-scoped row in the finale block, and Our Grand
 * Concert's is the row KI-71 corrected to `our_grand_concert` at `480b711`. The branch now reads the
 * catalogue rather than the config entry, because the catalogue is where the fact lives.
 *
 * These cases pin the three answers and, by giving URA the same treatment as Grand Concert, that the
 * branch is driven by the row's presence rather than by a scenario name. A scenario name in here would
 * be a second source for the layout path, which D-240 forbids.
 */
function finaleAbsenceRun(string $scenario): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

function seedFinaleRow(string $scenarioKey): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Catalogue finale row',
        'is_mandatory' => true,
    ]);
}

const CALENDAR_SENTENCE = 'The finale is on the career calendar as a mandatory race; this scenario declares no structure beyond it, so nothing further is drawn.';
const NO_STRUCTURE_SENTENCE = 'This scenario declares no finale structure, so none is shown.';

it('names the finale on the calendar instead of denying one exists', function (): void {
    $run = finaleAbsenceRun('our_grand_concert');
    seedFinaleRow('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Nothing was invented to satisfy the sentence: the config key stays undeclared, because
            // no component reads its contents today. The payload key is `finale_structure` since
            // Slice 23; `config/scenarios.php` still spells its own entry `finale`.
            ->where('scenario.finale_structure', null)
            ->where('scenario.finale_absence', CALENDAR_SENTENCE)
        );
});

it('denies a finale only when the catalogue really holds none for the scenario', function (): void {
    $run = finaleAbsenceRun('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale_absence', NO_STRUCTURE_SENTENCE)
        );
});

it('reads the catalogue for every scenario, not a list of names', function (): void {
    $run = finaleAbsenceRun('ura_finale');
    seedFinaleRow('ura_finale');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale_absence', CALENDAR_SENTENCE)
        );
});

it('says nothing where the scenario does declare a finale structure', function (): void {
    $run = finaleAbsenceRun('trackblazer');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Asserted through the declared key rather than `is_array($finale)`: at this depth the
            // Inertia test helper hands a nested array back as a Collection, so a shape closure
            // would fail for the wrong reason.
            ->where('scenario.finale_structure.kind', 'points_league')
            ->where('scenario.finale_absence', null)
        );
});

it('ignores a finale row that belongs to another scenario', function (): void {
    $run = finaleAbsenceRun('unity_cup');
    seedFinaleRow('trackblazer');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale_absence', NO_STRUCTURE_SENTENCE)
        );
});
