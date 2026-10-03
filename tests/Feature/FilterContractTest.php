<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\Umamusume;
use App\Services\PageSize;

/*
 * SCREEN_SPEC.md §7-9: three filter surfaces, two contracts. The catalog answered an unknown
 * `status` with the GlobalReleased default and no signal; skills and support cards refused theirs
 * and redirected. `pageSize` was clamped 1..100 on two surfaces and a fixed 25 on the third.
 *
 * ADR-0018 picks one contract: a facet value outside the accepted set is refused and lands on the
 * canonical route, and a page size is honoured and clamped, never refused. These cases are the
 * contract, stated once for all three surfaces, because the failure mode of a shared rule is a
 * surface drifting back to its old local behaviour while its own tests still pass.
 */

it('clamps every page size into the one window', function (mixed $raw, int $expected): void {
    expect(PageSize::clamp($raw))->toBe($expected);
})->with([
    'absent is the default' => [null, 25],
    'blank is the floor, as it was on both surfaces' => ['', 1],
    'non numeric is the floor' => ['banana', 1],
    'zero is the floor' => [0, 1],
    'negative is the floor' => ['-4', 1],
    'in range passes through' => ['3', 3],
    'above the ceiling is the ceiling' => ['500', 100],
    'the ceiling itself passes' => ['100', 100],
]);

it('refuses an unknown catalog status and lands on the canonical catalog', function (): void {
    Umamusume::factory()->create(['release_status' => ReleaseStatus::GlobalReleased]);

    $this->get('/umamusume?status=Bogus')
        ->assertRedirect(route('catalog.index'))
        ->assertSessionHasErrors('status');

    $this->followingRedirects()->get('/umamusume?status=Bogus')
        ->assertOk()
        ->assertSee('Release status:');
});

it('still accepts every status the picker offers, including the unfiltered token', function (string $status): void {
    $this->get("/umamusume?status={$status}")->assertOk()->assertSessionHasNoErrors();
})->with(['all', 'GlobalReleased', 'GlobalAnnounced', 'JapanOnly']);

it('treats an empty status as no filter chosen rather than a bad value', function (): void {
    $this->get('/umamusume?status=')
        ->assertOk()
        ->assertSessionHasNoErrors();
});

it('refuses an unknown skill type and lands on the canonical skills screen', function (): void {
    $this->get('/skills?type=Debuff')
        ->assertRedirect(route('skills.index'))
        ->assertSessionHasErrors('type');
});

it('refuses an unknown support-card type and lands on the canonical card screen', function (): void {
    $this->get('/support-cards?type=turbo')
        ->assertRedirect(route('support-cards.index'))
        ->assertSessionHasErrors('type');
});

it('honours a page size on the surface that used to ignore it', function (): void {
    SupportCard::factory()->count(4)->create();

    $this->get('/support-cards?pageSize=3')
        ->assertOk()
        ->assertViewHas('cards', fn ($cards): bool => $cards->count() === 3 && $cards->perPage() === 3);

    // The default stays where the deleted constant had it, so a filter nobody touched does not
    // change shape underneath SupportCardPageTest's own pagination case.
    expect($this->get('/support-cards')->assertOk()->viewData('cards')->perPage())->toBe(25);
});

it('keeps the catalog and skills page sizes on the same rule they already used', function (): void {
    Umamusume::factory()->count(3)->create();
    Skill::factory()->count(3)->create(['release_status' => 'GlobalReleased', 'name_is_client' => true]);

    $this->get('/umamusume?pageSize=2')
        ->assertOk()
        ->assertViewHas('umamusumes', fn ($rows): bool => $rows->perPage() === 2);

    $this->get('/skills?pageSize=2')
        ->assertOk()
        ->assertViewHas('skills', fn ($rows): bool => $rows->perPage() === 2);
});
