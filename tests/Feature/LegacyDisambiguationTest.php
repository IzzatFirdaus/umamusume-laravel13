<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Telling two same-named ancestors apart.
 *
 * The client can show two `Vodka` rows in one ancestry diagram, and a record that stores only the name
 * cannot tell them apart. An optional `ancestors_meta` companion carries each ancestor's id and costume
 * beside its name, and the parent picker's options carry the same distinguishing label.
 */
it('preserves distinct ids for two ancestors the client spells the same', function (): void {
    $payload = LegacySelectionPayload::fromArray([
        'legacies' => [[
            'rank' => 4,
            'is_guest' => false,
            'ancestors' => ['Vodka', 'Vodka'],
            'ancestors_meta' => [
                ['id' => 11, 'name' => 'Vodka', 'costume' => 'New Year'],
                ['id' => 22, 'name' => 'Vodka', 'costume' => 'Maid'],
            ],
            'sparks' => [],
        ]],
        'affinity' => null,
    ]);

    $meta = $payload->toArray()['legacies'][0]['ancestors_meta'];

    expect($meta)->toHaveCount(2)
        ->and($meta[0]['id'])->toBe(11)
        ->and($meta[1]['id'])->toBe(22)
        ->and($meta[0]['id'])->not->toBe($meta[1]['id'])
        ->and($meta[0]['costume'])->toBe('New Year')
        ->and($meta[1]['costume'])->toBe('Maid');
});

it('still loads a payload that carries names only', function (): void {
    $payload = LegacySelectionPayload::fromArray([
        'legacies' => [[
            'rank' => null,
            'is_guest' => false,
            'ancestors' => ['Vodka', null],
            'sparks' => [],
        ]],
        'affinity' => null,
    ]);

    expect($payload->legacies[0]['ancestors'])->toBe(['Vodka', null])
        ->and(array_key_exists('ancestors_meta', $payload->legacies[0]))->toBeFalse();
});

it('refuses an ancestors_meta companion that does not line up with the names', function (): void {
    LegacySelectionPayload::fromArray([
        'legacies' => [[
            'rank' => null,
            'is_guest' => false,
            'ancestors' => ['Vodka'],
            'ancestors_meta' => [
                ['id' => 11, 'name' => 'Vodka', 'costume' => null],
                ['id' => 22, 'name' => 'Vodka', 'costume' => null],
            ],
            'sparks' => [],
        ]],
        'affinity' => null,
    ]);
})->throws(InvalidArgumentException::class, 'names 1 ancestors but carries 2 meta rows');

it('labels two same-named parent options distinctly by id', function (): void {
    $trainee = Umamusume::factory()->create(['name' => 'Vodka']);
    $runA = TrainingRun::factory()->create(['umamusume_id' => $trainee->id, 'status' => RunStatus::Completed]);
    $runB = TrainingRun::factory()->create(['umamusume_id' => $trainee->id, 'status' => RunStatus::Completed]);
    Veteran::factory()->create(['training_run_id' => $runA->id]);
    Veteran::factory()->create(['training_run_id' => $runB->id]);

    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/LegacySelect')
            ->has('roster', 2)
            ->where('roster', function ($roster): bool {
                $rows = collect($roster)->values();

                return $rows->count() === 2
                    && str_contains((string) $rows[0]['label'], 'Vodka')
                    && str_contains((string) $rows[1]['label'], 'Vodka')
                    && $rows[0]['label'] !== $rows[1]['label'];
            }));
});
