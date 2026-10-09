<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\CreateSnapshotRun;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\SnapshotFieldState;
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
    /**
     * The display labels for the state fields, in the order the review screen reads them.
     *
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'speed' => 'Speed',
        'stamina' => 'Stamina',
        'power' => 'Power',
        'guts' => 'Guts',
        'wit' => 'Wit',
        'energy' => 'Energy',
        'fans' => 'Fans',
        'skill_points' => 'Skill Points',
    ];

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
            // The setup form's submit now opens the review, which is where the snapshot is read back
            // before it is written; `action` stays the commit endpoint the review posts to.
            'review' => route('career.snapshot.review'),
            'action' => route('career.snapshot.store'),
        ]);
    }

    /**
     * The read-back the Trainer confirms before committing.
     *
     * It is a GET over the form the setup screen submitted, so the same `StoreSnapshotRequest`
     * validates it here: a snapshot missing its position is refused with the field named before any
     * row is written, not after. The validated payload travels to the page so the confirm button
     * re-posts exactly what was read back rather than re-collecting it.
     */
    public function review(StoreSnapshotRequest $request): Response
    {
        $data = $request->validated();

        return Inertia::render('Career/Snapshot/Review', [
            'fields' => $this->reviewFields($data),
            'payload' => $data,
            'action' => route('career.snapshot.store'),
            'back' => route('career.snapshot.setup'),
        ]);
    }

    public function store(StoreSnapshotRequest $request): RedirectResponse
    {
        $run = (new CreateSnapshotRun)->handle($request->validated());

        return redirect()
            ->route('runs.cockpit', $run)
            ->with('status', 'Snapshot recorded.');
    }

    /**
     * One row per state field: its label, its three-way state, and the exact string the screen prints.
     *
     * The three states are distinct sentences, never three renderings of an absence: Known names the
     * value, Unknown says the Trainer looked and the client shows nothing, and Not provided says the
     * field was left alone. A reviewer can therefore tell "I don't know this" from "I didn't fill it in".
     *
     * @param  array<string, mixed>  $data
     * @return list<array{key: string, label: string, state: string, state_label: string, value: int|null, display: string}>
     */
    private function reviewFields(array $data): array
    {
        $states = is_array($data['field_states'] ?? null) ? $data['field_states'] : [];

        $rows = [];

        foreach (StoreSnapshotRequest::STATE_FIELDS as $field) {
            $label = self::FIELD_LABELS[$field];

            $state = is_string($states[$field] ?? null)
                ? (SnapshotFieldState::tryFrom($states[$field]) ?? SnapshotFieldState::NotProvided)
                : (($data[$field] ?? null) === null || ($data[$field] ?? null) === ''
                    ? SnapshotFieldState::NotProvided
                    : SnapshotFieldState::Known);

            $value = $state === SnapshotFieldState::Known ? (int) $data[$field] : null;

            $rows[] = [
                'key' => $field,
                'label' => $label,
                'state' => $state->value,
                'state_label' => $state->label(),
                'value' => $value,
                'display' => match ($state) {
                    SnapshotFieldState::Known => "Known · {$label} {$value}",
                    SnapshotFieldState::Unknown => "Unknown · {$label}",
                    SnapshotFieldState::NotProvided => "Not provided · {$label}",
                },
            ];
        }

        return $rows;
    }
}
