<?php

declare(strict_types=1);

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

it('renders the three confidence states as three distinct labels', function (): void {
    $this->get(route('career.snapshot.review', reviewPayload()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Review')
            ->where('fields.0.display', 'Known · Speed 607')
            ->where('fields.3.display', 'Unknown · Guts')
            ->where('fields.5.display', 'Not provided · Energy')
            ->where('fields.3.state', 'unknown')
            ->where('fields.5.state', 'not_provided'));
});

it('keeps Unknown and Not provided apart rather than rendering both as one absence', function (): void {
    $this->get(route('career.snapshot.review', reviewPayload()))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Review');

            $fields = $page->toArray()['props']['fields'];

            expect(collect($fields)->firstWhere('key', 'guts')['display'])->toBe('Unknown · Guts')
                ->and(collect($fields)->firstWhere('key', 'energy')['display'])->toBe('Not provided · Energy')
                ->and(collect($fields)->firstWhere('key', 'guts')['display'])
                ->not->toBe(collect($fields)->firstWhere('key', 'energy')['display']);
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

    $this->get(route('career.snapshot.review', $payload))
        ->assertSessionHasErrors('career_phase');
});
