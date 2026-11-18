<?php

declare(strict_types=1);

use App\Actions\CreateSnapshotRun;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The snapshot's read-back step: the screen between the form and the write.
 *
 * The three states it renders are the point of the slice. Known names a number the Trainer read,
 * Unknown says the client showed nothing for the field, and Not provided says the field was left
 * alone; they are three different sentences about one career, and the review must not collapse them
 * into one blank. The store keeps the distinction in the run's own position payload.
 */
function reviewPayload(array $overrides = []): array
{
    return $overrides + [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'speed' => 607,
        'stamina' => 500,
        'power' => 500,
        'guts' => null,
        'wit' => null,
        'energy' => null,
        'fans' => null,
        'skill_points' => null,
        'field_states' => [
            'speed' => 'known',
            'stamina' => 'known',
            'power' => 'known',
            'guts' => 'unknown',
            'wit' => 'unknown',
            'energy' => 'not_provided',
            'fans' => 'not_provided',
            'skill_points' => 'not_provided',
        ],
    ];
}

/**
 * KI-164: read one review row by the key its controller wrote, never by its array position.
 * The identity block grew twice (R7-99, then U-03's rarity and Potential Level) and each growth
 * cost edits to `fields.N` in four tests that had nothing to do with identity. A keyed read makes
 * an added row invisible to assertions about unrelated fields.
 *
 * @param  list<array{key: string, display: string, state: string}>  $fields
 * @return array{key: string, display: string, state: string}
 */
function reviewField(array $fields, string $key): array
{
    $row = collect($fields)->firstWhere('key', $key);

    expect($row, "review fields carry no row keyed {$key}")->not->toBeNull();

    return $row;
}

it('renders the three confidence states as three distinct labels', function (): void {
    $this->post(route('career.snapshot.review'), reviewPayload())
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Review');

            $fields = $page->toArray()['props']['fields'];

            expect(reviewField($fields, 'speed')['display'])->toBe('Known · Speed 607')
                ->and(reviewField($fields, 'guts')['display'])->toBe('Unknown · Guts')
                ->and(reviewField($fields, 'energy')['display'])->toBe('Not provided · Energy')
                ->and(reviewField($fields, 'guts')['state'])->toBe('unknown')
                ->and(reviewField($fields, 'energy')['state'])->toBe('not_provided');
        });
});

it('keeps keyed assertions valid when a new row is prepended', function (): void {
    // KI-164's acceptance case: a payload row added ahead of the stat rows must not require any
    // assertion about speed or energy to be re-derived.
    $this->post(route('career.snapshot.review'), reviewPayload())
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $fields = $page->toArray()['props']['fields'];
            $withNewRow = array_merge([
                ['key' => 'new_fact', 'display' => 'Known · New fact', 'state' => 'known'],
            ], $fields);

            expect(reviewField($withNewRow, 'speed')['display'])
                ->toBe(reviewField($fields, 'speed')['display'])
                ->and(reviewField($withNewRow, 'energy')['display'])
                ->toBe('Not provided · Energy')
                ->and(reviewField($withNewRow, 'new_fact')['display'])->toBe('Known · New fact');
        });
});

it('keeps Unknown and Not provided apart rather than rendering both as one absence', function (): void {
    $this->post(route('career.snapshot.review'), reviewPayload())
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Review');

            $fields = $page->toArray()['props']['fields'];

            expect(reviewField($fields, 'guts')['display'])->toBe('Unknown · Guts')
                ->and(reviewField($fields, 'energy')['display'])->toBe('Not provided · Energy')
                ->and(reviewField($fields, 'guts')['display'])
                ->not->toBe(reviewField($fields, 'energy')['display']);
        });
});

it('persists each field\'s state on the run it writes', function (): void {
    $this->post('/career/snapshot', reviewPayload())->assertRedirect();

    $run = TrainingRun::query()->latest('id')->first();

    $states = $run->careerPosition()->fieldStates;

    expect($states['speed'])->toBe('known')
        ->and($states['guts'])->toBe('unknown')
        ->and($states['energy'])->toBe('not_provided');
});

it('writes no turn entry when a core stat was not read, rather than inventing a zero', function (): void {
    $this->post('/career/snapshot', reviewPayload())->assertRedirect();

    $run = TrainingRun::query()->latest('id')->first();

    expect($run->turnEntries()->count())->toBe(0);
});

it('refuses a snapshot missing a required field at the review step', function (): void {
    $payload = reviewPayload();
    unset($payload['career_phase']);

    $this->post(route('career.snapshot.review'), $payload)
        ->assertSessionHasErrors('career_phase');
});

it('names the snapshot position through the same owner the race header reads', function (): void {
    // R4-08 date half: the read-back names the position the Trainer entered, and the cockpit reads the
    // same words for the run it writes, so the two cannot spell the month two ways.
    // All five core stats known, so the write logs a turn entry at 67 and the dashboard reads the
    // same turn the review named.
    $payload = reviewPayload([
        'guts' => 500,
        'wit' => 500,
        'field_states' => [
            'speed' => 'known',
            'stamina' => 'known',
            'power' => 'known',
            'guts' => 'known',
            'wit' => 'known',
            'energy' => 'not_provided',
            'fans' => 'not_provided',
            'skill_points' => 'not_provided',
        ],
    ]);

    $this->post(route('career.snapshot.review'), $payload)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Review')
            ->where('position_label', "Senior Year \u{00B7} Early October"));

    $run = (new CreateSnapshotRun)->handle($payload);

    // The race header splits the same label into its two halves; composed, they must read exactly
    // as the review's single sentence. The Dashboard is not a valid comparison: its
    // `position_label` is built from the logged-turn count, not the career position.
    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextTurn.year_label', 'Senior Year')
            ->where('nextTurn.position_label', 'Early October'));
});

it('opens on a bare visit with every field not provided instead of bouncing to the form', function (): void {
    // The 2026-10-28 parity audit found this URL rendering the Setup heading: the GET used the
    // submit-time FormRequest, so an empty query failed validation and the redirect landed on the
    // form. The HFW's review is a real state with nothing entered, and every field reads
    // "Not provided" there.
    $this->get(route('career.snapshot.review'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Review')
                ->where('has_draft', false)
                ->where('position_label', null)
                ->has('fields', 12)
                ->where('entry', route('career.snapshot'));

            $fields = $page->toArray()['props']['fields'];

            expect(reviewField($fields, 'speed')['display'])->toBe('Not provided · Speed')
                ->and(reviewField($fields, 'skill_points')['display'])->toBe('Not provided · Skill Points');
        });
});

it('reads back the session draft after the setup form opened the review once', function (): void {
    $this->post(route('career.snapshot.review'), reviewPayload())->assertOk();

    $this->get(route('career.snapshot.review'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->where('has_draft', true);

            expect(reviewField($page->toArray()['props']['fields'], 'speed')['display'])
                ->toBe('Known · Speed 607');
        });
});

it('reads the submitted body and refuses to read a payload carried in the query string', function (): void {
    // AUD-20261102-008 F-01. Notes take 5000 characters, and a URL puts all of them in the browser
    // history and in every request log between the form and the review. The review reads the request
    // body only, so a query-string payload is not a payload the route honours.
    $this->post(route('career.snapshot.review'), reviewPayload(['notes' => 'read from the body only']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Review')
            ->where('payload.notes', 'read from the body only'));

    $this->get(route('career.snapshot.review', reviewPayload(['notes' => 'must not be read from a URL'])))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('has_draft', true)
            ->where('payload.notes', 'read from the body only'));
});

it('reads back the trainee and the scenario it will write (R7-99)', function (): void {
    // The review's own copy claims "This is exactly what will be written. Read it back." Both identity
    // fields are `required` in `StoreSnapshotRequest::fieldRules()`, and the payload carries them — but
    // `reviewFields()` walked only `STATE_FIELDS`, so neither appeared in the read-back. A Trainer could
    // not confirm the two things the snapshot is *about*. (Round 7 R7-99; snapshot walk §7.3 S04.)
    $trainee = Umamusume::factory()->create(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']);

    $this->post(route('career.snapshot.review.submit'), reviewPayload(['umamusume_id' => $trainee->id]))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Review');

            $fields = $page->toArray()['props']['fields'];

            expect(reviewField($fields, 'umamusume_id')['key'])->toBe('umamusume_id')
                ->and(reviewField($fields, 'umamusume_id')['display'])
                ->toBe('Known · Trainee Rice Shower')
                ->and(reviewField($fields, 'scenario')['key'])->toBe('scenario')
                // Config's label, never the stored slug.
                ->and(reviewField($fields, 'scenario')['display'])
                ->toBe('Known · Scenario '.config('scenarios.scenarios.unity_cup.label'));
        });
});
