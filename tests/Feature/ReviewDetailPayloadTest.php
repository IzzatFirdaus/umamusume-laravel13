<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The review detail's comparison payload (S10).
 *
 * The page asks one question — is this the right trainee? — and it cannot answer it from the
 * candidate row alone. It needs the record the engine proposed and the other forms that share the
 * name, because a costume is a bracketed prefix inside `name` rather than a column: two forms of one
 * trainee share a name and differ in growth, so collapsing them is what would make the costume tag
 * meaningless.
 *
 * What these tests pin is the payload's honesty: a proposed record is either there whole or absent,
 * the same-trainee rows are listed apart, and a name with no registered alias says so instead of
 * leaving the row blank.
 */

it('sends the proposed record with its costume split out of the name', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => '[Rosy Dreams] Rice Shower',
        'slug' => 'rice-shower-rosy-dreams',
        'name_ja' => 'ライスシャワー',
    ]);

    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => '[Rosy Dreams] Rice Shower',
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Review/Detail')
            ->where('bestMatch.id', $umamusume->id)
            ->where('bestMatch.slug', 'rice-shower-rosy-dreams')
            ->where('bestMatch.name_ja', 'ライスシャワー')
            ->where('bestMatch.costume', 'Rosy Dreams')
            ->has('bestMatch.aptitudes')
        );
});

/*
 * The rarity mark (`review-detail.html:90`, `pill pill-neutral "★★★ SSR"`). The rarity lives on the
 * character card, not on `umamusume`, so `show()` eager-loads `suggestedUmamusume.cards` and derives
 * the maximum with `CatalogController`'s own expression. Both halves are pinned: a trainee with cards
 * prints the highest card's label and star count, and a trainee with none prints null so the page can
 * state the absence rather than a zero-star mark.
 */
it('sends the proposed record max rarity from its character cards', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Rice Shower', 'slug' => 'rice-shower']);

    CharacterCard::factory()->for($umamusume)->create(['rarity' => CardRarity::OneStar]);
    CharacterCard::factory()->for($umamusume)->create(['rarity' => CardRarity::ThreeStar]);

    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => 'Rice Shower',
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('bestMatch.max_rarity.label', CardRarity::ThreeStar->label())
            ->where('bestMatch.max_rarity.stars', CardRarity::ThreeStar->stars())
        );
});

it('sends a null rarity when the proposed record has no card', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'No Card', 'slug' => 'no-card']);

    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => 'No Card',
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page->where('bestMatch.max_rarity', null));
});

it('lists the other forms of the same trainee apart from the proposed one', function (): void {
    $chosen = Umamusume::factory()->create(['name' => '[Rosy Dreams] Rice Shower', 'slug' => 'rosy']);
    $other = Umamusume::factory()->create(['name' => '[Base] Rice Shower', 'slug' => 'base']);

    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => '[Rosy Dreams] Rice Shower',
        'suggested_umamusume_id' => $chosen->id,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('otherForms', 1)
            ->where('otherForms.0.id', $other->id)
            ->where('otherForms.0.costume', 'Base')
        );
});

it('says a name has no registered alias rather than leaving the row out', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Rice Shower']);
    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => 'Rice Shower',
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page->where('hasAlias', false));

    UmamusumeAlias::factory()->create(['umamusume_id' => $umamusume->id, 'alias' => 'Rice Shower']);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page->where('hasAlias', true));
});

it('sends an absent best match as null rather than an empty record', function (): void {
    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => 'Nobody At All',
        'suggested_umamusume_id' => null,
    ]);

    test()->get("/review/{$candidate->id}")
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('bestMatch', null)
            ->has('otherForms', 0)
        );
});
