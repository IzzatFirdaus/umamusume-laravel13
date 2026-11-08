<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Typed Spark targets.
 *
 * The target category is a transient form field: the payload stores the target string verbatim
 * (`ADR-0010`), but the write refuses a target that does not belong to the category the Trainer picked,
 * so `Stat -> Long` cannot be stored at the boundary.
 */
function sparkTargetPayload(string $category, string $target): array
{
    return [
        'affinity' => null,
        'legacies' => [
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'ancestors_sparks' => [[], []],
                'sparks' => [
                    ['kind' => 'blue', 'category' => $category, 'target' => $target, 'stars' => 1],
                ],
            ],
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'ancestors_sparks' => [[], []],
                'sparks' => [],
            ],
        ],
    ];
}

it('refuses a Stat Spark whose target is not a stat', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Stat', 'Long'))
        ->assertSessionHasErrors(['legacies.0.sparks.0.target']);
});

it('accepts a Stat Spark naming a stat', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Stat', 'Speed'))
        ->assertSessionHasNoErrors();
});

it('accepts an Aptitude Spark naming an aptitude', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Aptitude', 'Long'))
        ->assertSessionHasNoErrors();
});

it('refuses an Aptitude Spark whose target is not an aptitude', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Aptitude', 'Speed'))
        ->assertSessionHasErrors(['legacies.0.sparks.0.target']);
});

it('accepts a pick naming a Spark that exists', function (): void {
    // One Spark is recorded, so index 0 names it (`ADR-0010` Amendment 2026-10-31).
    $payload = sparkTargetPayload('Stat', 'Speed');
    $payload['legacies'][0]['pick'] = 0;

    $this->put(route('career.legacy.store'), $payload)
        ->assertSessionHasNoErrors();
});

it('refuses a pick at or beyond the recorded Sparks', function (): void {
    // One Spark exists, so index 1 is not a Spark. A stored index that names nothing would render as
    // a parent with a headline factor she does not carry, which is why the boundary refuses it
    // rather than storing it and letting the reader cope.
    $payload = sparkTargetPayload('Stat', 'Speed');
    $payload['legacies'][0]['pick'] = 1;

    $this->put(route('career.legacy.store'), $payload)
        ->assertSessionHasErrors(['legacies.0.pick']);
});

/*
 * S-A-P02.1 — the Skill target list is the Global client's catalogue, not the whole table.
 *
 * The picker offers these names and the write accepts any string, so the list is the only thing
 * standing between a Trainer and a Japan-only skill the shipped client cannot award
 * (`ADR-0011` §2, `Skill::scopeAvailableOnGlobal()`). Both doors assemble it: the wizard's
 * `LegacySelectController::show()` and the run-scoped `LegacyController::builder()`.
 */
it('offers only Global-client skills as Skill spark targets on the wizard step', function (): void {
    $global = Skill::factory()->create([
        'name' => 'Xceleration',
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    $japanOnly = Skill::factory()->create([
        'name' => 'Japan Only Sample',
        'release_status' => ReleaseStatus::JapanOnly,
        'name_is_client' => false,
    ]);

    $response = $this->get(route('career.legacy'));

    $response->assertInertia(function (Assert $page) use ($global, $japanOnly): void {
        $skills = $page->toArray()['props']['sparkTargets']['Skill'];

        expect($skills)->toContain($global->name)
            ->and($skills)->not->toContain($japanOnly->name);
    });
});

it('offers only Global-client skills as Skill spark targets on the run-scoped builder', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $trainee->id, 'scenario' => 'ura_finale']);

    $global = Skill::factory()->create([
        'name' => 'Global Sample Skill',
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    $japanOnly = Skill::factory()->create([
        'name' => 'Japan Only Sample Two',
        'release_status' => ReleaseStatus::JapanOnly,
        'name_is_client' => false,
    ]);

    $response = $this->get(route('legacy.builder', $run));

    $response->assertInertia(function (Assert $page) use ($global, $japanOnly): void {
        $skills = $page->toArray()['props']['sparkTargets']['Skill'];

        expect($skills)->toContain($global->name)
            ->and($skills)->not->toContain($japanOnly->name);
    });
});
