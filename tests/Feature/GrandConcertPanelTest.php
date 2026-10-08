<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\ScenarioCaps;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCR-017, the baseline strip the panel region draws when a scenario composes no panel (plan §9 E6,
 * D-241, gate G-41). This is the fourth `[Global]` scenario's whole surface: it composes no panel of
 * its own, so what it draws is the shell's empty branch.
 *
 * `ScenarioPanelTest` owns the shell's payload contract; this file owns the strip's content and the
 * G-41 property. The rendered copy, the 44px sweep, the 320px reflow and the axe scan live in
 * `tests/browser/grand-concert-panel.spec.ts`.
 */

function grandConcertRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);
}

it('turns every panel flag off, which is the state gate G-41 is about', function (): void {
    $run = grandConcertRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->where('scenario.panels', function (Collection $panels): bool {
                // The flag set is read from the matrix rather than repeated here, so a seventh flag
                // cannot rot this case. Every one of them off is what makes the region draw the strip
                // instead of a panel.
                expect(array_keys($panels->all()))->toBe(array_keys(config('scenarios.panel_labels')));
                expect(array_filter($panels->all(), fn (array $panel): bool => $panel['on'] === true))->toBe([]);

                return true;
            });

            // The other half of G-41: the strip is there, with its own name, its caps and its reason.
            $page->where('scenario.label', 'Our Grand Concert')
                ->where('scenario.declared', true)
                ->has('scenario.caps.rows', 5)
                ->where('scenario.panel_absence', fn ($absence): bool => is_string($absence) && $absence !== '')
                ->where('scenario.panel_absence_title', fn ($title): bool => is_string($title) && $title !== '');
        });
});

it('prints the five published caps as base plus bonus, read through the same owner the validator uses', function (): void {
    $run = grandConcertRun();
    $caps = ScenarioCaps::caps('our_grand_concert');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($caps): void {
            $page->where('scenario.caps.verified_at', config('scenarios.verified_at'))
                ->where('scenario.caps.base', 1200)
                ->where('scenario.caps.rows', function (Collection $rows) use ($caps): bool {
                    expect($rows)->toHaveCount(5);

                    foreach ($rows as $row) {
                        // The arithmetic the strip prints, and the same ceiling the stat bars and the
                        // turn validator read (ADR-0015), so the two cannot disagree about a stat.
                        expect($row['base'] + $row['bonus'])->toBe($row['cap']);
                        expect($row['cap'])->toBe($caps[$row['key']]);
                    }

                    return true;
                });
        });
});

it('cites where the caps come from rather than printing numbers with no provenance', function (): void {
    $run = grandConcertRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.caps.source_title', fn (string $title): bool => str_contains($title, 'config/scenarios.php'))
        );
});

it('carries no invented mechanic: no song, lesson or token string reaches the payload', function (): void {
    $run = grandConcertRun();

    // A floor, not a proof. The scenario's mechanics are sourced (docs/scenarios/07-grand-concert.md,
    // refilled 2026-10-05), so the risk this guards is not "the guide is empty" but "someone rendered
    // the guide": these are the words a panel would need, and no measured client string supplies them.
    $unmeasured = ['song', 'lesson', 'performance token', 'promotional live', 'hype'];

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($unmeasured): void {
            $payload = json_encode($page->toArray()['props'], JSON_THROW_ON_ERROR);

            foreach ($unmeasured as $word) {
                expect(stripos($payload, $word))->toBeFalse();
            }
        });
});

it('declares the absence only for the scenario that has one', function (): void {
    // The other three scenarios compose panels, so they have no line to print and the strip is not
    // reached for them. The keys travel null rather than absent, so the section keeps one shape.
    foreach (['ura_finale', 'unity_cup', 'trackblazer'] as $key) {
        $run = grandConcertRun($key);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.panel_absence', null)
                ->where('scenario.panel_absence_title', null)
            );
    }
});

it('draws the strip for a fifth scenario from config alone', function (): void {
    // G-33's acceptance case, asserted at the strip rather than at the shell: one config entry with
    // every flag off and no component edit. The strip's keys arrive for the new entry and nothing
    // scenario-specific does.
    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => array_fill_keys(array_keys(config('scenarios.panel_labels')), false),
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
    ]);

    $run = grandConcertRun('fifth_scenario');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->where('scenario.label', 'A Fifth Scenario')
                ->where('scenario.declared', true)
                ->where('scenario.documented', true)
                ->where('scenario.panel_absence', null)
                ->has('scenario.caps.rows', 5);

            $page->where('scenario.panels', function (Collection $panels): bool {
                expect(array_filter($panels->all(), fn (array $panel): bool => $panel['on'] === true))->toBe([]);

                return true;
            });
        });
});
