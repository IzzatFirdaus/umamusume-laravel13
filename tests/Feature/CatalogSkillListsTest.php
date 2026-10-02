<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;

/*
 * The trainee page's Skills section, widened from two card lists to four.
 *
 * The skill detail page reads these columns in reverse; this is the forward read of the same
 * lists, and the two surfaces have to agree about which band a skill sits in.
 * `CatalogDetailPageTest` already pins the two original headings and the section-level absence
 * sentence, so the cases here cover only what changed: the two new groups, the per-group
 * absence, the id that resolves to no catalogue row, and the Unique pill the row component has
 * always drawn while the controller never selected the column behind it.
 */

/**
 * A trainee with one form carrying the given skill lists.
 *
 * @param  array<string, mixed>  $lists
 */
function skillListTrainee(array $lists): Umamusume
{
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
    ]);

    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id] + $lists);

    return $umamusume;
}

it('renders all four skill groups on her page when the form carries them', function (): void {
    $umamusume = skillListTrainee([
        'skills_unique' => [910001],
        'skills_innate' => [910002],
        'skills_awakening' => [910003],
        'skills_event' => [910004],
    ]);

    foreach ([910001, 910002, 910003, 910004] as $id) {
        Skill::factory()->create(['export_id' => $id, 'name' => 'Test Skill '.$id]);
    }

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Her unique skills')
        ->assertSee('Her innate skills')
        ->assertSee('Her awakening skills')
        ->assertSee('Her event skills')
        ->assertSee('Test Skill 910001')
        ->assertSee('Test Skill 910002')
        ->assertSee('Test Skill 910003')
        ->assertSee('Test Skill 910004');
});

it('gives a list the form does not carry its own absence sentence', function (): void {
    $umamusume = skillListTrainee([
        'skills_unique' => [910011],
        'skills_innate' => [910012],
    ]);

    Skill::factory()->create(['export_id' => 910011, 'name' => 'Test Group Unique Skill']);
    Skill::factory()->create(['export_id' => 910012, 'name' => 'Test Group Innate Skill']);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        // Two groups have content, so the section is drawn and the two empty lists say so
        // themselves rather than leaving a heading with nothing under it.
        ->assertSee('No awakening skills recorded for this form.')
        ->assertSee('No event skills recorded for this form.')
        ->assertDontSee('No unique skill recorded for this form.')
        ->assertDontSee('No innate skills recorded for this form.');
});

it('draws the Unique pill on her own unique skill', function (): void {
    $umamusume = skillListTrainee(['skills_unique' => [910021]]);
    Skill::factory()->create(['export_id' => 910021, 'name' => 'Test Real Unique', 'is_unique' => true]);

    // x-skill-row has always rendered the pill, and CatalogController never selected
    // `is_unique`, so the attribute arrived null and no trainee page could badge one.
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('✦ Unique', false);
});

it('says a list carries ids the catalogue cannot name instead of drawing a blank group', function (): void {
    $umamusume = skillListTrainee(['skills_event' => [919999]]);

    // Measured on the committed bodies, 1 of the 1,273 event ids resolves to no skill row,
    // so this is a state the data holds. `[]` from the lookup is not the same claim as an
    // empty list on the card, and no export id may print (D-30).
    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Her event skills')
        ->assertSee('Recorded on this form but not in the skill catalog yet.')
        ->assertDontSee('919999');
});

it('names a skill the Global client cannot meet without linking to a page that refuses it', function (): void {
    $umamusume = skillListTrainee(['skills_awakening' => [912345]]);
    $skill = Skill::factory()->create([
        'export_id' => 912345,
        'name' => 'Test JP Only Skill',
        'release_status' => ReleaseStatus::JapanOnly,
        'name_is_client' => false,
    ]);

    // The detail route serves only rows `Skill::availableOnGlobal()` accepts, which is the rule
    // ADR-0011 §2 states for a Trainer-facing surface. A link from her page to that route for a
    // JapanOnly skill is therefore a control that cannot do what it says: measured on the
    // committed bodies, 322 of 1,082 awakening references and 163 of 1,273 event references name
    // a row the route refuses. The name still prints; the href does not.
    $html = $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertSee('Test JP Only Skill')
        ->getContent();

    expect($html)->not->toContain('href="'.route('skills.show', $skill).'"');
});
