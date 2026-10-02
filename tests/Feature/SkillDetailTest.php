<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;

/*
 * The skill detail page: one Global skill's recorded fields, the trainees whose forms
 * carry it, and the absences the dataset holds.
 *
 * Rows are factory-built rather than imported through the pipeline because this page is
 * a read path over `skills` and `character_cards`, not the producer; the producer's own
 * behaviour is pinned in `GametoraSkillsParserTest` and `CharacterCardParserTest`. Every
 * Trainer-facing row here carries the two flags `Skill::availableOnGlobal()` reads,
 * because the route refuses the rest.
 */

/**
 * A Global skill the client itself names, the only kind the detail route serves.
 *
 * @param  array<string, mixed>  $attributes
 */
function skillDetailSkill(array $attributes = []): Skill
{
    return Skill::factory()->create(array_merge([
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ], $attributes));
}

/**
 * The visible text of the rendered body, so count-based assertions read the page a
 * Trainer reads rather than the `<head>`'s title element.
 */
function skillDetailBodyText(string $html): string
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);

    $body = $dom->getElementsByTagName('body')->item(0);

    return (string) preg_replace('/\s+/', ' ', $body === null ? '' : $body->textContent);
}

it('renders the recorded fields of a global skill on its detail page', function (): void {
    $skill = skillDetailSkill([
        'name' => 'Test Unique Skill',
        'name_ja' => 'テスト固有スキル',
        'type' => 'Speed',
        'sp_cost' => 180,
        'is_unique' => true,
        'export_id' => 987654,
        'source_url' => 'https://gametora.test/skills.json',
    ]);

    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertSee('Test Unique Skill')
        ->assertSee('テスト固有スキル')
        ->assertSee('Speed')
        ->assertSee('180 SP')
        ->assertSee('✦ Unique')
        ->assertSee('https://gametora.test/skills.json')
        // D-30: the export id is an engine key, and no export id reaches a screen.
        ->assertDontSee('987654');
});

it('states the absences the dataset holds instead of inventing values', function (): void {
    $skill = skillDetailSkill([
        'name' => 'Test Plain Skill',
        'name_ja' => 'Test Plain Skill',
        'type' => null,
        'sp_cost' => null,
        'export_id' => 987655,
    ]);

    // Whitespace-normalised, because the copy's phrases wrap across newlines in the
    // rendered markup and a raw containment check would fail on a line break, not on an absence.
    $html = (string) preg_replace(
        '/\s+/',
        ' ',
        $this->get(route('skills.show', $skill))->assertOk()->getContent(),
    );

    // D-220: an absent value renders as N/A with a title naming which kind of absence,
    // never as a default, a bare zero, the word Unknown, or the em dash (R-02/D-79).
    expect($html)->toContain('N/A')
        ->and($html)->toMatch('/title="[^"]*(no SP cost|no type)[^"]*"/i')
        ->and($html)->not->toContain('Unknown')
        ->and($html)->not->toContain('—')
        // The two name columns agree, so the pair prints once (the index's own rule).
        ->and(substr_count(skillDetailBodyText($html), 'Test Plain Skill'))->toBe(1)
        // The mechanics the source carries but this tool does not record are stated, with
        // the decision that records why named in the same sentence.
        ->and($html)->toContain('not recorded')
        ->and($html)->toContain('ADR-0011')
        // D-33 with nothing to show: the row names its own unattributed state.
        ->and($html)->toContain('seeded or entered by hand');
});

it('refuses a skill the global client cannot meet rather than rendering it', function (): void {
    $japanOnly = skillDetailSkill([
        'name' => 'Test JP Skill',
        'release_status' => ReleaseStatus::JapanOnly,
        'export_id' => 987656,
    ]);
    $notClientNamed = skillDetailSkill([
        'name' => 'Test Third-Party Skill',
        'name_is_client' => false,
        'export_id' => 987657,
    ]);

    // ADR-0011 §2: no row reaches a Trainer-facing surface that `availableOnGlobal()`
    // rejects, and this page is one of those surfaces. An unknown id is the same refusal.
    $this->get(route('skills.show', $japanOnly))->assertNotFound();
    $this->get(route('skills.show', $notClientNamed))->assertNotFound();
    $this->get(route('skills.show', 424242))->assertNotFound();
});

it('lists the trainees whose forms carry the skill, grouped by which list holds it', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Shared Skill', 'export_id' => 987658]);

    $uniqueHolder = Umamusume::factory()->create(['name' => 'Unique Holder', 'slug' => 'unique-holder']);
    $innateHolder = Umamusume::factory()->create(['name' => 'Innate Holder', 'slug' => 'innate-holder']);
    $otherHolder = Umamusume::factory()->create(['name' => 'Other Holder', 'slug' => 'other-holder']);
    $unconfirmedHolder = Umamusume::factory()->create(['name' => 'Unconfirmed Holder', 'slug' => 'unconfirmed-holder']);

    CharacterCard::factory()->create(['umamusume_id' => $uniqueHolder->id, 'skills_unique' => [987658]]);
    CharacterCard::factory()->create(['umamusume_id' => $uniqueHolder->id, 'skills_unique' => [987658]]);
    CharacterCard::factory()->create(['umamusume_id' => $innateHolder->id, 'skills_innate' => [987658]]);
    CharacterCard::factory()->create(['umamusume_id' => $otherHolder->id, 'skills_innate' => [100011]]);
    CharacterCard::factory()->unconfirmed()->create(['umamusume_id' => $unconfirmedHolder->id, 'skills_unique' => [987658]]);

    $html = $this->get(route('skills.show', $skill))->assertOk()->getContent();

    // The two lists answer two different questions, so the page keeps the card's own
    // grouping; the trainee is the link, deduplicated across her forms, and an
    // unconfirmed form is behind the same disclosure the catalog list applies.
    expect(skillDetailBodyText($html))->toContain('As her unique skill')
        ->and(skillDetailBodyText($html))->toContain('Among her innate skills')
        ->and(skillDetailBodyText($html))->toContain('Unique Holder')
        ->and(skillDetailBodyText($html))->toContain('Innate Holder')
        ->and(substr_count(skillDetailBodyText($html), 'Unique Holder'))->toBe(1)
        ->and($html)->toContain('href="'.route('catalog.show', 'unique-holder').'"')
        ->and($html)->not->toContain('Other Holder')
        ->and($html)->not->toContain('Unconfirmed Holder');
});

it('names the absence when no trainee record carries the skill', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Orphan Skill', 'export_id' => 987659]);

    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertSee('No trainees recorded with this skill.');
});

it('reaches the detail page from the skill search rows', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Linked Skill', 'export_id' => 987660]);

    $html = $this->get(route('skills.index'))->assertOk()->getContent();

    expect($html)->toContain('href="'.route('skills.show', $skill).'"');
});

it('reaches the detail page from a trainee page\'s skill rows', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'skills_unique' => [987661]]);
    $skill = skillDetailSkill(['name' => 'Test Catalog Skill', 'export_id' => 987661]);

    $html = $this->get(route('catalog.show', 'special-week'))->assertOk()->getContent();

    expect($html)->toContain('href="'.route('skills.show', $skill).'"');
});

it('renders the activation predicate and effect vector the source states', function (): void {
    $skill = skillDetailSkill([
        'name' => 'Test Mechanics Skill',
        'export_id' => 987662,
        'condition_groups' => [
            [
                'base_time' => 50000,
                'condition' => 'is_last_straight==1',
                'precondition' => 'order<=5',
                'effects' => [['type' => 27, 'value' => 4500]],
            ],
            [
                'base_time' => null,
                'condition' => 'phase>=2',
                'precondition' => null,
                'effects' => [['type' => 31, 'value' => 2000], ['type' => 27, 'value' => 1500]],
            ],
        ],
    ]);

    $html = $this->get(route('skills.show', $skill))->assertOk()->getContent();

    // Decoded, because the engine expressions carry `<` and `&` and Blade entity-escapes them, so a
    // raw containment check would fail on the encoding rather than on the absence (KI-21's failure).
    $visible = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // The source's own expressions, verbatim, and both activations when the record states two.
    expect($visible)->toContain('is_last_straight==1')
        ->and($visible)->toContain('order<=5')
        ->and($visible)->toContain('phase>=2')
        ->and($visible)->toContain('50000')
        ->and($visible)->toContain('4500')
        ->and($visible)->toContain('2000')
        // The description stays absent, and the page names the ruling that keeps it absent.
        ->and($visible)->toContain('G-SK-20')
        ->and($html)->not->toContain('—');
});

it('lists the trainees whose awakening and event lists carry the skill', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Granted Skill', 'export_id' => 987664]);

    $awakened = Umamusume::factory()->create(['name' => 'Awakened Holder', 'slug' => 'awakened-holder']);
    $evented = Umamusume::factory()->create(['name' => 'Event Holder', 'slug' => 'event-holder']);

    CharacterCard::factory()->create(['umamusume_id' => $awakened->id, 'skills_awakening' => [987664]]);
    CharacterCard::factory()->create(['umamusume_id' => $evented->id, 'skills_event' => [987664]]);

    $html = $this->get(route('skills.show', $skill))->assertOk()->getContent();

    expect(skillDetailBodyText($html))->toContain('Among her awakening skills')
        ->and(skillDetailBodyText($html))->toContain('Among her event skills')
        ->and(skillDetailBodyText($html))->toContain('Awakened Holder')
        ->and(skillDetailBodyText($html))->toContain('Event Holder')
        ->and($html)->toContain('href="'.route('catalog.show', 'awakened-holder').'"');
});
