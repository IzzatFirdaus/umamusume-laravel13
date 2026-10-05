<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * B.6: the skill picker collapses to one open catalogue and nine closed rows, the
 * same shape the deck panel landed in Part 6. A closed row still posts its value
 * under the same field name, so the POST contract is unchanged; the open/closed
 * DOM (one select, nine links) is the page component's and is carried by the
 * browser spec.
 */

it('collapses the skill picker to one open list and nine rows behind it', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $skills = Skill::factory()->count(9)->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    foreach ($skills as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Acquired);
    }

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // One catalogue list for every row, and the nine rows it fills.
            ->has('skillCatalog', 9)
            ->where('skillGroups', fn (Collection $groups): bool => $groups->sum(
                fn (array $group): int => count($group['skills'])
            ) === 9));
});

it('accepts the row the query names with every row still in the payload', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $skills = Skill::factory()->count(9)->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    foreach ($skills as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Acquired);
    }

    // `skill_row` is read client-side by the page component, so the server-side claim is that the
    // query is accepted, survives onto the page, and every row it can open rides in the payload.
    test()->get(route('runs.show', $run).'?skill_row=4')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->url(route('runs.show', ['run' => $run], false).'?skill_row=4')
            ->has('skillCatalog', 9)
            ->where('skillGroups', fn (Collection $groups): bool => $groups->sum(
                fn (array $group): int => count($group['skills'])
            ) === 9));
});
