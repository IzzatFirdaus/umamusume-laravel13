<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The competition-spark names as the client prints them in the canonical Rice Shower tree
 * (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:246-252`). Eighteen spark entries, fifteen distinct
 * strings. The catalogue's longer spellings are deliberately not accepted as substitutes.
 */
function sparkTargetEntryRaceNames(): array
{
    return [
        'Oka Sho', 'Victoria Mile', 'Shuka Sho', 'Japan C.', 'Osaka Hai',
        'Tenno Sho (Autumn)', 'Kikuka Sho', 'URA Finale', 'Takarazuka Kinen',
        'Asahi Hai F.S.', 'Tenno Sho (Spring)', 'NHK Mile C.', 'Sprinters S.',
        'JBC L. Classic', 'Tokyo Daishoten',
    ];
}

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
        $names = array_column($skills, 'name');

        expect($names)->toContain($global->name)
            ->and($names)->not->toContain($japanOnly->name);
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
        $names = array_column($skills, 'name');

        expect($names)->toContain($global->name)
            ->and($names)->not->toContain($japanOnly->name);
    });
});

/*
 * S-A-P02.5 — the Skill option list carries the Japanese name beside the English one.
 *
 * `SkillController.php:515` states the rule this case pins: *"The rule is search matches what the user
 * can see."* 605 of the 623 Global-client skills carry a `name_ja` that differs from `name`
 * (`Gourmand` / `食いしん坊`), and `DESIGN.md:573` already lists `name_ja` among the fields a skill row
 * prints. A picker that shows the Japanese name and cannot be searched by it is the rule's failure
 * inverted: the Trainer types what they see and nothing comes up.
 */
it('carries the Japanese name beside the English one for every Skill spark target', function (): void {
    $skill = Skill::factory()->create([
        'name' => 'Gourmand Sample',
        'name_ja' => '食いしん坊サンプル',
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    $response = $this->get(route('career.legacy'));

    $response->assertInertia(function (Assert $page) use ($skill): void {
        $skills = $page->toArray()['props']['sparkTargets']['Skill'];
        $row = null;

        foreach ($skills as $candidate) {
            if (($candidate['name'] ?? null) === $skill->name) {
                $row = $candidate;
                break;
            }
        }

        expect($row)->not->toBeNull()
            ->and($row['name_ja'])->toBe('食いしん坊サンプル');
    });
});

/*
 * The two gaps from the 2026-11-20 walk: no Category for a competition Spark, and no selectable
 * "Ignited Spirit: Guts +" target. Both target lists are sourced from config/uma.php and must be
 * identical on the wizard step and the run-scoped builder, because both post to the one request.
 */
it('offers the client-spelled race targets on the wizard step', function (): void {
    $this->get(route('career.legacy'))->assertInertia(function (Assert $page): void {
        $race = $page->toArray()['props']['sparkTargets']['Race'];

        expect($race)->toEqualCanonicalizing(sparkTargetEntryRaceNames())
            ->and($race)->not->toContain('NHK Mile Cup')
            ->and($race)->not->toContain('Asahi Hai Futurity Stakes');
    });
});

it('offers the same race targets on the run-scoped builder', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $trainee->id, 'scenario' => 'ura_finale']);

    $this->get(route('legacy.builder', $run))->assertInertia(function (Assert $page): void {
        expect($page->toArray()['props']['sparkTargets']['Race'])
            ->toEqualCanonicalizing(sparkTargetEntryRaceNames());
    });
});

it('offers the spark-only plus name beside the Global skills on both doors', function (): void {
    $check = static function (Assert $page): void {
        $skills = $page->toArray()['props']['sparkTargets']['Skill'];
        $names = array_column($skills, 'name');

        expect($names)->toContain('Ignited Spirit: Guts +');

        $plus = null;

        foreach ($skills as $candidate) {
            if (($candidate['name'] ?? null) === 'Ignited Spirit: Guts +') {
                $plus = $candidate;
                break;
            }
        }

        // The spark name is a client reading with no Japanese string in the export; the catalogue
        // rows beside it do carry one. No catalogue row is asserted here: the test DB is unseeded.
        expect($plus)->not->toBeNull()
            ->and($plus['name_ja'])->toBeNull();
    };

    $this->get(route('career.legacy'))->assertInertia($check);

    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $trainee->id, 'scenario' => 'ura_finale']);

    $this->get(route('legacy.builder', $run))->assertInertia($check);
});

it('stores a race name and a plus spark target verbatim', function (): void {
    foreach (['Oka Sho', 'Japan C.', 'Ignited Spirit: Guts +'] as $target) {
        $payload = sparkTargetPayload($target === 'Ignited Spirit: Guts +' ? 'Skill' : 'Race', $target);
        $payload['legacies'][0]['sparks'][0]['kind'] = 'white';

        $this->put(route('career.legacy.store'), $payload)->assertSessionHasNoErrors();

        $stored = SetupDraft::legacySelection();
        expect($stored['legacies'][0]['sparks'][0]['target'])->toBe($target)
            ->and($stored['legacies'][0]['sparks'][0]['kind'])->toBe('white');
    }
});

it('keeps Race and Skill targets free text at the write boundary', function (): void {
    // Client spellings are abbreviated, so a hand-made POST with an unlisted name is still stored
    // verbatim; only Stat and Aptitude resolve to an allowlist (`assertTargetMatchesCategory`).
    $this->put(route('career.legacy.store'), sparkTargetPayload('Race', 'An Unlisted Race'))
        ->assertSessionHasNoErrors();

    $this->put(route('career.legacy.store'), sparkTargetPayload('Skill', 'An Unlisted Spark'))
        ->assertSessionHasNoErrors();

    $stored = SetupDraft::legacySelection();
    expect($stored['legacies'][0]['sparks'][0]['target'])->toBe('An Unlisted Spark');
});
