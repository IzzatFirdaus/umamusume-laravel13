<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
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

it('does not tell the trainer that skill lists are not stored', function (): void {
    $umamusume = detailTrainee(2);

    // WS-2 Task 2.1. The page used to say the card document's skill id arrays "are not stored"
    // and that ADR-0012 kept them off the card row. Both halves are now false: the arrays landed
    // at dd90330 with casts on the model, so the page was describing a schema this tool no longer
    // has. The assertion targets the retired sentence rather than the word "stored", so the new
    // Skills body cannot accidentally satisfy it.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertDontSee('are not stored')
        ->assertDontSee('Skill lists are not stored');
});

it('names the card skill lists its Skills section reads from', function (): void {
    $umamusume = detailTrainee(1);

    // The replacement copy has to be specific rather than merely not-stale: it names the two
    // keys the tool now keeps and the two that stay unrecorded, so a reader can tell which
    // absence is a schema decision and which is a missing import.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('skills_innate')
        ->assertSee('skills_unique');
});

it('lists her unique and innate skills on her detail page', function (): void {
    $umamusume = detailTrainee(1);
    $umamusume->cards()->first()->update([
        'skills_unique' => [900001],
        'skills_innate' => [900002, 900003],
    ]);

    // Fixture names are obviously fake: a plausible-sounding invented skill name is
    // indistinguishable from a real one at a glance, and this repo does not store unsourced rows.
    Skill::factory()->create(['export_id' => 900001, 'name' => 'Test Unique Skill']);
    Skill::factory()->create(['export_id' => 900002, 'name' => 'Test Innate Skill A']);
    Skill::factory()->create(['export_id' => 900003, 'name' => 'Test Innate Skill B']);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Her unique skills')
        ->assertSee('Her innate skills')
        ->assertSee('Test Unique Skill')
        ->assertSee('Test Innate Skill A')
        ->assertSee('Test Innate Skill B');
});

it('says her skill lists are not recorded rather than drawing empty groups', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('No skill lists are recorded for this form.');
});

it('states the goal-race absence in the canonical form', function (): void {
    $umamusume = detailTrainee(1);

    // WS-2 Task 2.3: a heading plus the canonical absence body. No trainee_goals table exists;
    // KI-34 is the reservation for it.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Goal races')
        ->assertSee('Goal races are not recorded.');
});

it('lists her runs and offers one primary action to start another', function (): void {
    $umamusume = detailTrainee(1);
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
    ]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Her runs')
        ->assertSee('URA Finale')
        ->assertSee('New run')
        ->assertSee('Opens run setup; the trainee is chosen there.');
});

it('names the absence when she has no runs rather than drawing an empty list', function (): void {
    $umamusume = detailTrainee(1);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('No runs recorded for her yet.');
});

it('never renders the word Unknown for a missing value', function (): void {
    $umamusume = detailTrainee(1);

    // The absence vocabulary is binding on every WS-2 task: a missing value is "not recorded",
    // or N/A carrying a title. "Unknown" is neither, and the debut dates used to render it.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertDontSee('Unknown');
});

it('renders the eight sections in the binding order', function (): void {
    $umamusume = detailTrainee(1);

    // WS-2 Task 2.5. The order is the workstream head's, not the alphabetical list the task
    // checklist uses to sweep them.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSeeTextInOrder([
            'Basic information',
            'Aptitude',
            'Costume forms',
            'Skills',
            'Goal races',
            'Her runs',
            'Aliases',
            'Provenance',
        ]);
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
