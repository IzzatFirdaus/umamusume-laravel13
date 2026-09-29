<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;

/*
 * The detail page: the profile block above, the per-form content below, and a tab strip that
 * appears only above one form.
 *
 * The threshold is the user's own complaint — a page of stacked duplicates of the same
 * character — so both sides of it are pinned: two forms get a strip, one form gets the same
 * content with no chrome.
 */

/**
 * A trainee with the given number of forms, all released on Global.
 */
function detailTrainee(int $forms): Umamusume
{
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'slug' => 'special-week',
        'release_status' => ReleaseStatus::GlobalReleased,
    ]);

    foreach (range(1, $forms) as $index) {
        CharacterCard::factory()->create([
            'umamusume_id' => $umamusume->id,
            'card_id' => 900000 + $index,
            'title' => "[Global] Form {$index}",
            'rarity' => CardRarity::ThreeStar,
            'is_debut_form' => $index === 1,
            'global_release_date' => '2026-0'.$index.'-01',
        ]);
    }

    return $umamusume->fresh();
}

it('draws a tab strip when a trainee has more than one form', function (): void {
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('name="form"', false)
        ->assertSee('Open this form')
        ->assertSee('form-panel-'.$umamusume->cards->first()->id, false);
});

it('draws no tab strip for a trainee with a single form', function (): void {
    // The threshold. One form is not a choice, and a one-option control is chrome the Trainer
    // has to read past to reach the same content.
    $umamusume = detailTrainee(1);

    $response = $this->get(route('catalog.show', $umamusume->slug));

    $response->assertOk()
        ->assertDontSee('name="form"', false)
        ->assertDontSee('Open this form')
        // The content is still there: no chrome, not less page.
        ->assertSee('[Global] Form 1');
});

it('renders one panel per form, each carrying its own title', function (): void {
    $umamusume = detailTrainee(3);

    $response = $this->get(route('catalog.show', $umamusume->slug));

    $response->assertOk();

    foreach (['[Global] Form 1', '[Global] Form 2', '[Global] Form 3'] as $title) {
        $response->assertSee($title);
    }

    // Three radios, three panels: the strip is not a control over one reused body.
    expect(substr_count($response->getContent(), 'type="radio" name="form"'))->toBe(3)
        ->and(substr_count($response->getContent(), 'peer/t'))->toBeGreaterThan(3);
});

it('opens the first form by default and honours ?form=', function (): void {
    $umamusume = detailTrainee(3);
    $cards = $umamusume->cards;

    // Matched as a tag rather than as a substring: Blade puts each attribute on its own line, so
    // asserting "id=.. value=.. checked" as one string would be testing the template's whitespace.
    // Not preg_quoted, because the only interpolated value is an integer id and preg_quote would
    // escape this pattern's own `\s+` quantifiers into literals.
    $checkedRadio = fn (int $id): string => '<input type="radio" name="form" id="form-tab-'.$id.'" value="'.$id.'"\s+class="[^"]*"\s+aria-label="[^"]*"\s+checked>';

    // Default: the first card in the controller's scope order is the checked radio.
    $default = $this->get(route('catalog.show', $umamusume->slug))->assertOk()->getContent();

    expect($default)->toMatch('/'.$checkedRadio($cards->first()->id).'/');

    // Addressable: a link into the second form survives a reload.
    $addressed = $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'form' => $cards[1]->id]))
        ->assertOk()->getContent();

    expect($addressed)->toMatch('/'.$checkedRadio($cards[1]->id).'/')
        ->and($addressed)->not->toMatch('/'.$checkedRadio($cards->first()->id).'/');
});

it('falls back to the first form for a ?form= that names no card on this page', function (): void {
    // A stale shared link should show the trainee, not a 404.
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'form' => 999999]))
        ->assertOk()
        ->assertSee('[Global] Form 1')
        ->assertSee('[Global] Form 2');
});

it('carries the unconfirmed opt-in into the strip so a tab change does not close it', function (): void {
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', ['slug' => $umamusume->slug, 'show_unconfirmed' => 1]))
        ->assertOk()
        ->assertSee('name="show_unconfirmed" value="1"', false);
});

it('renders the six profile fields for a complete profile', function (): void {
    $umamusume = detailTrainee(2);
    UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Japanese name')
        ->assertSee('スペシャルウィーク')
        ->assertSee('Voice actor')
        ->assertSee('和氣あず未')
        ->assertSee('EN: Azumi Waki')
        ->assertSee('Release date')
        ->assertSee('Birthday')
        ->assertSee('May 2, 1995')
        ->assertSee('Height')
        ->assertSee('158 cm')
        ->assertSee('Three sizes')
        ->assertSee('81 · 81 · 56 cm');
});

it('says a value is unpublished rather than drawing an empty slot', function (): void {
    // D-220. Measured: va_en and three_sizes are each absent on 10 of the 135 roster rows, so
    // this is the normal path for a Trainer, not an edge case.
    $umamusume = detailTrainee(1);
    UmamusumeProfile::factory()->partial()->create(['umamusume_id' => $umamusume->id]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('No English dub listed')
        ->assertSee('Not published by the source');
});

it('names the missing birth year instead of inventing one', function (): void {
    // `birth_year` is the only birthday part the source omits (17 of 163 rows). A January 1st
    // would be a date the source never stated.
    $umamusume = detailTrainee(1);
    UmamusumeProfile::factory()->withoutBirthYear()->create([
        'umamusume_id' => $umamusume->id,
        'birth_month' => 3,
        'birth_day' => 20,
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Mar 20')
        ->assertSee('year not published')
        ->assertDontSee('Mar 20, 2000');
});

it('names the fetch when no profile has been written', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('No profile recorded for this trainee yet')
        ->assertSee('gametora-character-profiles');
});

it('renders the ten aptitude letters on both rows of the grid', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->update([
        'aptitude_turf' => 'A',
        'aptitude_dirt' => 'B',
        'aptitude_sprint' => 'C',
        'aptitude_mile' => 'D',
        'aptitude_medium' => 'E',
        'aptitude_long' => 'F',
        'aptitude_front_runner' => 'G',
        'aptitude_pace_chaser' => 'A',
        'aptitude_late_surger' => 'B',
        'aptitude_end_closer' => 'C',
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Aptitude')
        ->assertSee('Turf')
        ->assertSee('End closer')
        // All ten letters, each read from the trainee rather than the card.
        ->assertSeeTextInOrder(['Turf', 'A', 'End closer', 'C']);
});

it('says aptitude is unpublished rather than drawing a half grid', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Aptitude not published for this trainee.');
});

it('names this form\'s own source and read date (D-33)', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('https://gametora.test/character-cards.json');
});

it('states the skills deferral in words instead of drawing four empty groups', function (): void {
    $umamusume = detailTrainee(2);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Skill lists are not shown')
        ->assertSee('ADR-0012');
});

it('keeps the single-form page free of any tab markup', function (): void {
    $umamusume = detailTrainee(1);

    $content = $this->get(route('catalog.show', $umamusume->slug))->getContent();

    expect($content)->not->toContain('type="radio"')
        ->and($content)->not->toContain('<form method="GET"');
});

it('still prints the Japanese name before the profile fetch has run', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'slug' => 'special-week',
        'release_status' => ReleaseStatus::GlobalReleased,
    ]);

    expect($umamusume->fresh()->profile)->toBeNull();

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Japanese name')
        ->assertSee('スペシャルウィーク')
        ->assertSee('No profile recorded for this trainee yet');
});

it('prefers the profile document and falls back to the trainee column for the Japanese name', function (): void {
    $umamusume = detailTrainee(1);
    $profile = UmamusumeProfile::factory()->create([
        'umamusume_id' => $umamusume->id,
        'name_ja' => 'スペシャルウィーク・改',
    ]);

    expect($umamusume->fresh()->japaneseName())->toBe('スペシャルウィーク・改');

    $profile->update(['name_ja' => null]);

    expect($umamusume->fresh()->japaneseName())->toBe('スペシャルウィーク');
});
