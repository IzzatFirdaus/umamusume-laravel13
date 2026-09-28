<?php

declare(strict_types=1);

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TrainingRunController;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::view('/', 'welcome')->name('home');

Route::get('/umamusume', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/umamusume/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/training-runs', [TrainingRunController::class, 'index'])->name('runs.index');
Route::get('/training-runs/create', [TrainingRunController::class, 'create'])->name('runs.create');
Route::post('/training-runs', [TrainingRunController::class, 'store'])->name('runs.store');
Route::get('/training-runs/{run}', [TrainingRunController::class, 'show'])->name('runs.show');
Route::put('/training-runs/{run}', [TrainingRunController::class, 'update'])->name('runs.update');
Route::delete('/training-runs/{run}', [TrainingRunController::class, 'destroy'])->name('runs.destroy');
Route::get('/training-runs/{run}/export/{format}', [TrainingRunController::class, 'export'])->name('runs.export');
Route::post('/training-runs/{run}/turns', [TrainingRunController::class, 'storeTurn'])->name('runs.turns.store');
Route::put('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'updateTurn'])->name('runs.turns.update');
Route::delete('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'destroyTurn'])->name('runs.turns.destroy');
Route::post('/training-runs/{run}/skills', [TrainingRunController::class, 'syncSkills'])->name('runs.skills.sync');
Route::post('/training-runs/{run}/races', [TrainingRunController::class, 'storeRace'])->name('runs.races.store');
Route::post('/training-runs/{run}/purchases', [TrainingRunController::class, 'storePurchase'])->name('runs.purchases.store');

Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review/{candidate}', [ReviewController::class, 'resolve'])->name('review.resolve');

/*
 * ============================================================================
 * TEMPORARY REVIEW SURFACE. DELETE THIS ROUTE WHEN THE REAL RUN SCREEN LANDS.
 * ============================================================================
 *
 * Present so the Phase 1-4 output could be opened in a browser and measured. It
 * is a closure rather than a controller because the owner scoped this phase out
 * of backend work, and it reads config/scenarios.php plus literal sample values,
 * so it touches no database, no model and no migration.
 *
 * Delete together with:
 *   resources/views/design-preview.blade.php
 *   any component that turns out to be used only by it
 *
 * It is NOT a spec for the real run screen. The real screen should come from the
 * TrainingRun controller with an actual run, and it should reuse the four
 * components, not this page's data assembly.
 */
Route::get('/design-preview', function (): View {
    $scenarios = config('scenarios.scenarios');
    $stats = config('scenarios.stat_order');

    $samples = [];

    foreach ($scenarios as $key => $def) {
        $caps = array_map(fn (int $bonus): int => config('scenarios.base_cap') + $bonus, $def['cap_bonus']);

        // Literal sample values, chosen to exercise the halved-gains region and the
        // ceiling-equals-base case rather than to flatter the design.
        $values = [
            'ura_finale' => ['Speed' => 912, 'Stamina' => 640, 'Power' => 705, 'Guts' => 588, 'Wit' => 612],
            'unity_cup' => ['Speed' => 1044, 'Stamina' => 812, 'Power' => 977, 'Guts' => 704, 'Wit' => 1340],
            'trackblazer' => ['Speed' => 902, 'Stamina' => 1516, 'Power' => 868, 'Guts' => 740, 'Wit' => 1030],
            'our_grand_concert' => ['Speed' => 1104, 'Stamina' => 712, 'Power' => 866, 'Guts' => 790, 'Wit' => 742],
        ][$key] ?? array_fill_keys($stats, 0);

        $run = ['turn' => 27, 'energy' => 78, 'fans' => 41250];

        if ($key === 'unity_cup') {
            $run = array_merge($run, ['turn' => 31, 'energy' => 54, 'fans' => 66900, 'team_rank' => 'S+', 'bursts' => 8]);
        }

        if ($key === 'trackblazer') {
            $run = array_merge($run, ['turn' => 34, 'energy' => 41, 'fans' => 128400, 'grade_points' => 240, 'shop_coins' => 185]);
        }

        if ($key === 'our_grand_concert') {
            $run = array_merge($run, ['energy' => 66, 'fans' => 52300]);
        }

        // Twelve months, Early and Late, so the grid is genuinely 24 cells.
        $calendar = [];
        $states = [
            2 => ['Early' => ['state' => 'goal', 'label' => 'Fuwa Fuji Taima Stakes']],
            4 => ['Late' => ['state' => 'fan_locked', 'label' => 'Tenno Sho', 'fans_needed' => 12000]],
            5 => ['Early' => ['state' => 'maiden_locked', 'label' => 'Naruta Kinpa Cup']],
            3 => ['Late' => ['state' => 'open', 'label' => 'Entry open']],
            1 => ['Late' => ['state' => 'current', 'label' => 'Next']],
        ];

        for ($month = 0; $month < 12; $month++) {
            $calendar[] = [
                'halves' => [
                    'Early' => $states[$month]['Early'] ?? ['state' => 'empty'],
                    'Late' => $states[$month]['Late'] ?? ['state' => 'empty'],
                ],
            ];
        }

        $choices = [];

        if ($def['steps'][0] === 'facility') {
            foreach ($stats as $i => $stat) {
                $choices[] = [
                    'key' => 'facility-'.$stat,
                    'label' => $stat.' Work',
                    'detail' => 'Facility level set by team rank',
                    'facility_level' => [4, 3, 5, 4, 5][$i],
                    'present' => [3, 2, 5, 2, 4][$i],
                ];
            }
        } else {
            foreach ($stats as $stat) {
                $choices[] = [
                    'key' => 'training-'.$stat,
                    'label' => $stat.' Work',
                    'detail' => 'Facility level from repetition',
                    'facility_level' => 3,
                ];
            }
        }

        $choices[] = ['key' => 'rest', 'label' => 'Rest', 'detail' => 'Recovers Energy, spends the turn'];
        $choices[] = [
            'key' => 'mood',
            // Client label unverified: Recreation or Outing. D-20 blocks promoting either,
            // so the neutral phrase is used and the gap is shown rather than hidden.
            'label' => 'Mood adjustment',
            'detail' => 'Raises Mood. Client label unverified',
        ];

        $preview = [
            ['direction' => 'up', 'text' => '+8 Speed'],
            ['direction' => 'up', 'text' => '+4 Power'],
            ['direction' => 'up', 'text' => '+2 Skill Points'],
            ['direction' => 'down', 'text' => '-19 Energy'],
        ];

        // The Grade Point ladder, composed from the same two config blocks the
        // meter reads, so the review surface and the run screen cannot drift.
        $labels = $def['grade_objective_labels'] ?? [];
        $standard = $def['grade_objectives']['standard'] ?? [];

        $objectives = array_merge(
            [['name' => $labels['debut'] ?? 'Debut race', 'required' => 0]],
            array_map(
                fn (string $year, int $points): array => ['name' => $labels[$year] ?? $year, 'required' => $points],
                array_keys($standard),
                array_values($standard),
            ),
        );

        // The step under review comes from ?step= so every scenario-owned step can
        // be opened without editing this file. An unknown key falls back to the
        // scenario's first step rather than rendering a step the scenario lacks.
        $requested = request()->query('step');
        $step = in_array($requested, $def['steps'], true) ? $requested : $def['steps'][0];

        $samples[$key] = [
            'label' => $def['label'],
            'live' => $def['live_on_global'],
            'links' => $def['scenario_links'],
            'run' => $run,
            'values' => $values,
            'caps' => $caps,
            'sp' => 68,
            'calendar' => $calendar,
            'steps' => $def['steps'],
            'step' => $step,
            'objectives' => $objectives,
            'selected' => $choices[0]['key'],
            'choices' => $choices,
            'preview' => $preview,
            'note' => $def['notes'],
        ];
    }

    return view('design-preview', ['samples' => $samples]);
})->name('design.preview');
