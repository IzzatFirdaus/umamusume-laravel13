<?php

declare(strict_types=1);

use App\Models\Legacy\LegacySelectionPayload;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Schema;

/*
 * D-260 puts Legacy Select before the first turn: create run, choose scenario, choose Trainee,
 * choose Legacies. D-268 then says the screen holds more than `training_runs` can: per Legacy the
 * chosen Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with
 * per-Spark kind, target and star rank, plus an affinity grade for the pair — and that the two
 * character ids on the run do not meet Phase 4's provenance requirement.
 *
 * This is the schema half of that gap, and only the schema half. It gives the screen somewhere to
 * write; it builds no screen and computes nothing, which is what PRD §6 non-goal 3 actually bans.
 * ADR-0010 records the reading and the limits.
 *
 * Shape, per the owner's ruling for this session: one typed json payload keyed to the run rather
 * than a wide table of nullable columns, with the existing `inheritance_parent_a_id` / `_b_id`
 * foreign keys left in place instead of a second pair added beside them.
 */

it('holds the Legacy Select read-back in one json column on training_runs', function (): void {
    expect(Schema::hasColumn('training_runs', 'legacy_selection'))->toBeTrue();
});

it('keeps the two inheritance parent foreign keys instead of adding a second pair', function (): void {
    $columns = collect(Schema::getColumnListing('training_runs'));

    expect($columns)->toContain('inheritance_parent_a_id', 'inheritance_parent_b_id')
        // The incoming brief named these two. The ruling was to keep the foreign keys the table
        // already has rather than carry two names for one relationship.
        ->and($columns)->not->toContain('legacy_parent_a_id', 'legacy_parent_b_id');

    $targets = collect(Schema::getForeignKeys('training_runs'))
        ->map(fn (array $fk): string => ($fk['columns'][0] ?? '').'->'.$fk['foreign_table']);

    expect($targets)->toContain('inheritance_parent_a_id->umamusume')
        ->and($targets)->toContain('inheritance_parent_b_id->umamusume');
});

it('mass-assigns a full Legacy Select read-back and reads it back unchanged', function (): void {
    // Through `create`, not the factory: a factory write reaches the columns regardless of the
    // attribute, so only this path can show the payload is actually writable by the app. This is the
    // brief's case — a run created with two legacy parents and a sparks payload — with the parents on
    // the foreign keys the table already has rather than on a duplicated pair.
    $run = TrainingRun::create([
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'ura_finale',
        'inheritance_parent_a_id' => Umamusume::factory()->create()->id,
        'inheritance_parent_b_id' => Umamusume::factory()->create()->id,
        'legacy_selection' => legacyPayload(),
    ]);

    expect($run->fresh()->legacy_selection)->toBe(legacyPayload())
        ->and($run->inheritanceParentA)->not->toBeNull()
        ->and($run->inheritanceParentB)->not->toBeNull()
        ->and($run->legacySelection()->legacies[0]['sparks'])->toHaveCount(2);
});

it('exposes the stored payload as a typed object', function (): void {
    $payload = legacyRun(legacyPayload())->legacySelection();

    expect($payload)->toBeInstanceOf(LegacySelectionPayload::class)
        ->and($payload->affinity)->toBe('◎')
        ->and($payload->legacies)->toHaveCount(2)
        ->and($payload->legacies[0]['rank'])->toBe(3)
        ->and($payload->legacies[0]['is_guest'])->toBeTrue()
        ->and($payload->legacies[0]['ancestors'])->toBe(['Special Week', 'Silence Suzuka'])
        ->and($payload->legacies[0]['sparks'])->toHaveCount(2)
        // Entry order is the contract with the two foreign keys, because the payload carries no id:
        // this entry is parent B, and it reads as the empty record the screen shows when a slot is
        // open rather than as a Legacy with no sparks.
        ->and($payload->legacies[1]['sparks'])->toBe([]);
});

it('reports no Legacy Select at all rather than an empty one', function (): void {
    // D-220: an absent fact renders as absent. A run created before this column, or one whose
    // Trainer never opened the screen, has no read-back — and `[]` would render as a Legacy Select
    // holding zero Legacies, which is a claim about a screen that cannot be completed that way.
    expect(TrainingRun::factory()->create()->legacySelection())->toBeNull();
});

it('round-trips the stored shape through the typed object and back', function (): void {
    expect(LegacySelectionPayload::fromArray(legacyPayload())->toArray())->toBe(legacyPayload());
});

it('refuses a payload whose shape it cannot read', function (array $broken, string $reason): void {
    // A json column accepts any key set, so a typo becomes a row that reads as nothing. Validated
    // on the way in, the way `TurnEvent` validates its typed payloads — the precedent, not a new rule.
    expect(fn () => LegacySelectionPayload::fromArray($broken))
        ->toThrow(InvalidArgumentException::class, $reason);
})->with([
    'an unknown key' => [
        ['legacies' => [], 'affinity' => null, 'legacyies' => []],
        'unknown key',
    ],
    'an affinity outside the three grades' => [
        ['legacies' => [], 'affinity' => '★'],
        'affinity',
    ],
    'a spark kind outside the five the Global client renders' => [
        ['legacies' => [[
            'rank' => null, 'is_guest' => false, 'ancestors' => [],
            'sparks' => [['kind' => 'gold', 'target' => null, 'stars' => 1]],
        ]], 'affinity' => null],
        'spark kind',
    ],
    'stars above the three-star ceiling' => [
        ['legacies' => [[
            'rank' => null, 'is_guest' => false, 'ancestors' => [],
            'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 4]],
        ]], 'affinity' => null],
        'stars',
    ],
    'a legacy record missing its keys' => [
        ['legacies' => [['rank' => null, 'sparks' => []]], 'affinity' => null],
        'missing',
    ],
    'more ancestors than the diagram holds' => [
        ['legacies' => [[
            'rank' => null, 'is_guest' => false,
            'ancestors' => ['a', 'b', 'c'], 'sparks' => [],
        ]], 'affinity' => null],
        'ancestors',
    ],
    'an ancestor stored as a slot record rather than a name' => [
        // The shape the Inheritance page used to read and threw on. ADR-0010 §2 says an ancestor is a
        // name; a `slot`/`name` record is a second shape nothing writes, so it is refused here.
        ['legacies' => [[
            'rank' => null, 'is_guest' => false,
            'ancestors' => [['slot' => 'grandparent_a1', 'name' => 'Symboli Rudolf']], 'sparks' => [],
        ]], 'affinity' => null],
        'not a name',
    ],
]);

it('keeps a payload with an unread figure out of the exception path', function (): void {
    // Every field is nullable because the Trainer may not have read it yet, and a screen half-read
    // is a real state the tool must hold rather than reject.
    $halfRead = [
        'legacies' => [[
            'rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => [],
        ]],
        'affinity' => null,
    ];

    expect(legacyRun($halfRead)->legacySelection()->toArray())->toBe($halfRead);
});

/**
 * @return array<string, mixed>
 */
function legacyPayload(): array
{
    return [
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => true,
                'ancestors' => ['Special Week', 'Silence Suzuka'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                    ['kind' => 'green', 'target' => 'Marching Twinkle', 'stars' => 1],
                ],
            ],
            [
                'rank' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'sparks' => [],
            ],
        ],
        'affinity' => '◎',
    ];
}

function legacyRun(array $payload): TrainingRun
{
    return TrainingRun::factory()->create(['legacy_selection' => $payload]);
}
