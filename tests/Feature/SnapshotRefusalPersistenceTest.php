<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Session;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * S1.1 (N3.35): what survives a refused snapshot commit.
 *
 * The flow is setup -> POST review -> POST store. `review()` parks the validated payload in the
 * session (`career.snapshot.draft`), and `setup()` reads that same key back. A first submission that
 * reaches `store()` and fails validation therefore still has the draft parked: the form the Trainer
 * returns to must hold every value they entered, including a recorded zero (a zero is a reading, not
 * an absence) and the Unknown flags (they are the three-state vocabulary, not blank fields).
 *
 * These cases pin that survival and the write boundary: no run is created on a refused commit, and a
 * successful commit is unchanged. They also pin the one honest gap in the Inertia contract: a failed
 * `store()` lands the errors on the REVIEW page (the referrer), so the Trainer corrects there; the
 * parked draft is what makes the setup form whole when they go Back.
 */

function snapshotStorePayload(array $overrides = []): array
{
    return $overrides + [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        // A recorded zero: the field was read, the client showed 0. It must survive as Known zero,
        // never collapse into a not-provided blank.
        'energy' => 0,
        'field_states' => [
            'speed' => 'known',
            'stamina' => 'known',
            'power' => 'known',
            'guts' => 'unknown',
            'wit' => 'unknown',
            'energy' => 'known',
            'fans' => 'not_provided',
            'skill_points' => 'not_provided',
        ],
        'notes' => 'Read at the Narita gate.',
    ];
}

it('keeps the parked draft intact when the commit is refused, so Back shows what was entered', function (): void {
    $payload = snapshotStorePayload();

    // The setup screen posted the form to the review, which parked it.
    $this->post(route('career.snapshot.review'), $payload)->assertOk();

    // The commit is then refused: a payload tampered past the review's bounds (an Energy figure no
    // client can print).
    $bad = $payload;
    $bad['energy'] = 101;

    $this->post(route('career.snapshot.store'), $bad)
        ->assertSessionHasErrors('energy');

    // Nothing was written.
    expect(TrainingRun::query()->count())->toBe(0);

    // The draft the review parked is untouched by the refused commit, so returning to the setup form
    // re-renders every value the Trainer entered — including the recorded zero and the Unknown flags.
    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Setup')
            ->where('draft.energy', 0)
            ->where('draft.field_states.energy', 'known')
            ->where('draft.field_states.guts', 'unknown')
            ->where('draft.field_states.fans', 'not_provided')
            ->where('draft.notes', 'Read at the Narita gate.'));
});

it('refuses the commit without writing a run, and the successful commit is unchanged', function (): void {
    // Refused: no row, no turn entry.
    $this->post(route('career.snapshot.review'), snapshotStorePayload())->assertOk();
    $bad = snapshotStorePayload();
    $bad['career_phase'] = 'Nope';

    $this->post(route('career.snapshot.store'), $bad)
        ->assertSessionHasErrors('career_phase');

    expect(TrainingRun::query()->count())->toBe(0);

    // Then the honest payload commits exactly as the existing contract already did.
    $this->post(route('career.snapshot.review'), snapshotStorePayload())->assertOk();
    $this->post(route('career.snapshot.store'), snapshotStorePayload())->assertRedirect();

    expect(TrainingRun::query()->count())->toBe(1);
});

it('records a zero the Trainer read as Known zero, not as an absence', function (): void {
    // The three-state contract: a zero with state `known` is a reading. Only a field left with no
    // state entry is not provided.
    $payload = snapshotStorePayload([
        'speed' => 0,
        'field_states' => [
            'speed' => 'known',
            'stamina' => 'known',
            'power' => 'known',
            'guts' => 'not_provided',
            'wit' => 'not_provided',
            'energy' => 'known',
            'fans' => 'not_provided',
            'skill_points' => 'not_provided',
        ],
    ]);

    $this->post(route('career.snapshot.review'), $payload)->assertOk();
    $this->post(route('career.snapshot.store'), $payload)->assertRedirect();

    $run = TrainingRun::query()->latest('id')->first();

    expect($run->careerPosition()->fieldStates['speed'])->toBe('known')
        ->and($run->careerPosition()->fieldStates['energy'])->toBe('known');
});
