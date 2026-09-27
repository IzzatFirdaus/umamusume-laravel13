<?php

declare(strict_types=1);

use App\Enums\AliasLanguage;
use App\Enums\MatchTier;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\Umamusume;

/*
 * Views printed the enum backing value, so a Trainer read the machine token
 * `GlobalReleased` instead of words. These pin the display layer to human text
 * while keeping the backing value where it is load-bearing: the submitted option
 * value and the query-string filter. That is why the leak assertions target the
 * `>text<` node and not the page as a whole, which must still contain the value.
 */

it('labels every case of every rendered enum in human words', function (AliasLanguage|MatchTier|ReleaseStatus|RunStatus|SkillAcquisition $case): void {
    expect($case->label())
        ->toBeString()
        ->not->toBeEmpty()
        ->not->toStartWith('uma.')
        ->not->toMatch('/[a-z][A-Z]/');
})->with([
    ...ReleaseStatus::cases(),
    ...RunStatus::cases(),
    ...SkillAcquisition::cases(),
    ...AliasLanguage::cases(),
    ...MatchTier::cases(),
]);

it('names each release status in the words the Global trainer expects', function (): void {
    expect(ReleaseStatus::GlobalReleased->label())->toBe('Released (Global)')
        ->and(ReleaseStatus::GlobalAnnounced->label())->toBe('Announced (Global)')
        ->and(ReleaseStatus::JapanOnly->label())->toBe('Japan only');
});

it('renders the catalog dropdown label as text, not as the machine value', function (): void {
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);

    test()->get('/umamusume')
        ->assertOk()
        ->assertSee('Japan only')
        ->assertDontSee('>JapanOnly<', false)
        ->assertDontSee('>GlobalReleased<', false);
});

it('renders the detail page without any release-status machine token', function (): void {
    Umamusume::factory()->japanOnly()->create([
        'name' => 'Japan Only One',
        'slug' => 'japan-only-one',
    ]);

    test()->get('/umamusume/japan-only-one')
        ->assertOk()
        ->assertSee('Japan only')
        ->assertDontSee('JapanOnly');
});

it('keeps the backing value in the option value and in the status filter', function (): void {
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);
    Umamusume::factory()->create(['name' => 'Released One', 'slug' => 'released-one']);

    test()->get('/umamusume')->assertSee('value="JapanOnly"', false);

    test()->get('/umamusume?status=JapanOnly')
        ->assertOk()
        ->assertSee('Japan Only One')
        ->assertDontSee('Released One');
});
