<?php

declare(strict_types=1);

use App\Models\Skill;
use Database\Seeders\SkillSeeder;

/*
 * F-3, two defects in one file.
 *
 * A. The retired-name cleanup at SkillSeeder.php:75-80 deletes by name with no is_manual
 * predicate. The floor, no engine write to rows with is_manual = true, is in
 * docs/research-scratch/GOVERNANCE.md, CONSTRAINTS section "Floor",
 * and PRD FR-B-4 is the same rule. `Traightaways` and `Playtime's Over` are names this seeder
 * deletes on purpose, and both are also names a Trainer could plausibly have corrected by hand.
 *
 * B. The upsert at SkillSeeder.php:61-68 writes sp_cost = null and is_unique = false through
 * updateOrCreate. On a database where umamusume:fetch gametora-skills has already run, that
 * replaces the imported cost and uniqueness of all nine named rows with nulls. The docblock at
 * :34-36 explains why a seed row carries no cost, which is true of the seed row and false of
 * the effect on a row the import owns.
 */

function seededSkill(string $name, array $attributes = []): Skill
{
    /** @var Skill $skill */
    $skill = Skill::factory()->create(array_merge([
        'name' => $name,
        'is_manual' => false,
        'release_status' => 'GlobalReleased',
        'name_is_client' => true,
    ], $attributes));

    return $skill;
}

it('leaves imported cost and uniqueness alone when it re-seeds a known name', function (): void {
    seededSkill('Gourmand', ['sp_cost' => 100, 'is_unique' => true, 'export_id' => 201351]);

    (new SkillSeeder)->run();

    $skill = Skill::where('name', 'Gourmand')->sole();

    // F-3 B: the upsert nulls both today, so these are null and false rather than 100 and true.
    expect($skill->sp_cost)->toBe(100)
        ->and($skill->is_unique)->toBeTrue()
        ->and(Skill::where('name', 'Gourmand')->count())->toBe(1);
});

it('keeps a hand-corrected skill that the retired-name cleanup targets', function (): void {
    seededSkill('Traightaways', ['is_manual' => true, 'sp_cost' => 80]);
    // The cleanup opens with a LIKE clause and closes with named equals. Chaining
    // `where('is_manual', false)` after those orWhere() tests binds AND tighter than OR, so the guard
    // would reach only the last name: a manual row caught by the first clause would still be deleted.
    seededSkill('Illustrative Recovery Skill', ['is_manual' => true]);

    (new SkillSeeder)->run();

    // F-3 A: the delete runs unfiltered today, so both rows are gone after the seeder.
    expect(Skill::where('name', 'Traightaways')->count())->toBe(1)
        ->and(Skill::where('name', 'Traightaways')->sole()->is_manual)->toBeTrue()
        ->and(Skill::where('name', 'Illustrative Recovery Skill')->exists())->toBeTrue();
});

it('still removes the invented placeholder names it is meant to remove', function (): void {
    seededSkill('Illustrative Speed Skill');
    seededSkill('Traightaways');

    (new SkillSeeder)->run();

    // The canary for the guard above: a non-manual retired row must still be deleted, so a
    // test that only proves survival would pass on a seeder that deleted nothing at all.
    expect(Skill::where('name', 'Illustrative Speed Skill')->count())->toBe(0)
        ->and(Skill::where('name', 'Traightaways')->count())->toBe(0);
});
