<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The skill detail page's server-side contract: one Global skill's recorded fields, the
 * trainees whose forms carry it, and the absences the dataset holds, as the `Skills/Show`
 * page props.
 *
 * Rows are factory-built rather than imported through the pipeline because this page is
 * a read path over `skills` and `character_cards`, not the producer; the producer's own
 * behaviour is pinned in `GametoraSkillsParserTest` and `CharacterCardParserTest`. Every
 * Trainer-facing row here carries the two flags `Skill::availableOnGlobal()` reads,
 * because the route refuses the rest.
 *
 * What the page PRINTS for these props (the N/A titles, the "not recorded" sentence, the
 * name dedupe, the holder headings) is asserted against the rendered DOM in
 * `tests/browser/skills.spec.ts`; this file owns the payload those strings are built from.
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

it('passes the recorded fields of a global skill to the detail page', function (): void {
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.name', 'Test Unique Skill')
            ->where('skill.name_ja', 'テスト固有スキル')
            ->where('skill.type', 'Speed')
            ->where('skill.sp_cost', 180)
            ->where('skill.is_unique', true)
            ->where('skill.source_url', 'https://gametora.test/skills.json')
            // D-30: the export id is an engine key, and no export id reaches a screen.
            ->missing('skill.export_id'));
});

it('passes the absences the dataset holds instead of a default', function (): void {
    $skill = skillDetailSkill([
        'name' => 'Test Plain Skill',
        'name_ja' => 'Test Plain Skill',
        'type' => null,
        'sp_cost' => null,
        'export_id' => 987655,
    ]);

    // D-220: an absent value is null on the payload, so the page renders N/A with a title
    // naming which kind of absence rather than a default, a bare zero, or a dash.
    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->where('skill.type', null)
            ->where('skill.sp_cost', null)
            ->where('skill.source_url', null)
            ->where('skill.fetched_at_display', null));
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

it('passes the trainees whose forms carry the skill, grouped by which list holds it', function (): void {
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

    // The two lists answer two different questions, so the payload keeps the card's own
    // grouping; the trainee is deduplicated across her forms, and an unconfirmed form is
    // behind the same disclosure the catalog list applies, so it cannot surface here.
    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->has('uniqueHolders', 1)
            ->where('uniqueHolders.0.name', 'Unique Holder')
            ->where('uniqueHolders.0.slug', 'unique-holder')
            ->where('uniqueHolders.0.url', route('catalog.show', 'unique-holder'))
            ->has('innateHolders', 1)
            ->where('innateHolders.0.name', 'Innate Holder')
            ->has('awakeningHolders', 0)
            ->has('eventHolders', 0));
});

it('passes empty holder lists when no trainee record carries the skill', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Orphan Skill', 'export_id' => 987659]);

    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->has('uniqueHolders', 0)
            ->has('innateHolders', 0)
            ->has('awakeningHolders', 0)
            ->has('eventHolders', 0));
});

it('reaches the detail page from a trainee page\'s skill rows', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    CharacterCard::factory()->create(['umamusume_id' => $umamusume->id, 'skills_unique' => [987661]]);
    $skill = skillDetailSkill(['name' => 'Test Catalog Skill', 'export_id' => 987661]);

    // The row's link is the resolved `url` prop; a null there is a skill the route would refuse.
    $this->get(route('catalog.show', 'special-week'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skillLists.0.skills.0.url', route('skills.show', $skill)));
});

it('passes the activation predicate and effect vector the source states', function (): void {
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

    // The source's own expressions, verbatim, and both activations when the record states
    // two. The description stays absent and the page names the ruling that keeps it absent.
    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->has('skill.condition_groups', 2)
            ->where('skill.condition_groups.0.condition', 'is_last_straight==1')
            ->where('skill.condition_groups.0.precondition', 'order<=5')
            ->where('skill.condition_groups.0.base_time', 50000)
            ->where('skill.condition_groups.0.effects.0.type', 27)
            ->where('skill.condition_groups.0.effects.0.value', 4500)
            ->where('skill.condition_groups.1.condition', 'phase>=2')
            ->where('skill.condition_groups.1.base_time', null)
            ->where('skill.condition_groups.1.effects.1.type', 27)
            ->where('skill.condition_groups.1.effects.1.value', 1500));
});

it('passes the trainees whose awakening and event lists carry the skill', function (): void {
    $skill = skillDetailSkill(['name' => 'Test Granted Skill', 'export_id' => 987664]);

    $awakened = Umamusume::factory()->create(['name' => 'Awakened Holder', 'slug' => 'awakened-holder']);
    $evented = Umamusume::factory()->create(['name' => 'Event Holder', 'slug' => 'event-holder']);

    CharacterCard::factory()->create(['umamusume_id' => $awakened->id, 'skills_awakening' => [987664]]);
    CharacterCard::factory()->create(['umamusume_id' => $evented->id, 'skills_event' => [987664]]);

    $this->get(route('skills.show', $skill))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Skills/Show')
            ->has('awakeningHolders', 1)
            ->where('awakeningHolders.0.name', 'Awakened Holder')
            ->where('awakeningHolders.0.url', route('catalog.show', 'awakened-holder'))
            ->has('eventHolders', 1)
            ->where('eventHolders.0.name', 'Event Holder'));
});
