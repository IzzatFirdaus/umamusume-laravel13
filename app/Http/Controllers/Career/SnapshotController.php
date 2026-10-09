<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\CreateSnapshotRun;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSnapshotRequest;
use App\Models\Umamusume;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The snapshot entry point, from the Snapshot UI slice of the Rice Shower / Unity Cup remediation.
 *
 * The dashboard's single card routed every career through the new-career wizard, so a Trainer holding
 * a mid-career snapshot had to walk in through a door that assumes turn 1 - which is the audit's
 * finding that the mid-career position had no representation. This is the second door: an entry
 * screen that says what it is for, one form that records the snapshot in a single pass, and one
 * action that writes it.
 *
 * The write reuses the run's own surfaces rather than adding read paths: the position is stored on
 * the run, and the entered current state rides the run's turn log, so the cockpit, the timeline and
 * the training decision all read a snapshot the way they read any other run.
 */
class SnapshotController extends Controller
{
    public function entry(): Response
    {
        return Inertia::render('Career/Snapshot/Entry');
    }

    public function setup(): Response
    {
        return Inertia::render('Career/Snapshot/Setup', [
            'trainees' => Umamusume::query()->orderBy('name')->get(['id', 'name'])->values()->all(),
            'scenarios' => array_map(
                static fn (array $def): string => (string) $def['label'],
                (array) config('scenarios.scenarios'),
            ),
            'years' => array_map(
                static fn (CareerYear $year): array => ['value' => $year->value, 'label' => $year->label()],
                CareerYear::cases(),
            ),
            'phases' => array_map(
                static fn (CareerPhase $phase): array => ['value' => $phase->value, 'label' => $phase->value],
                CareerPhase::cases(),
            ),
            'action' => route('career.snapshot.store'),
        ]);
    }

    public function store(StoreSnapshotRequest $request): RedirectResponse
    {
        $run = (new CreateSnapshotRun)->handle($request->validated());

        return redirect()
            ->route('runs.cockpit', $run)
            ->with('status', 'Snapshot recorded.');
    }
}
