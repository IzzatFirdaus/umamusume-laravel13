<?php

declare(strict_types=1);

/*
 * U-02 / R8 (P-2 ruling) — a snapshot run's owned skills are added afterwards on the run's own planner,
 * and the planner records them as acquired.
 *
 * The record itself is deliberately not part of the snapshot: a snapshot is the career's position and
 * state, and the eleven skills a mid-career trainee already owns are a different fact with its own
 * write path (`runs.skills.sync`). What this file pins is that the second door works for a run that
 * arrived by snapshot — the door the review screen now points at — so the hand-off is a real route and
 * not a sentence.
 *
 * Two shapes worth recording. The add path posts `status: Acquired` from the planner
 * (`SkillsPlanner.vue:255`), so an added skill reads as owned rather than planned; and a level recorded
 * against it lives in `skill_levels` (ADR-0029 D4), never in the hint-discount ladder — Blue Rose
 * Closer Lv3 is the unique skill's own level, not a 10-40% cost discount.
 *
 * Top-level helpers carry this file's own prefix: an unprefixed global function collides with another
 * slice's and aborts the suite at load time.
 */

use App\Actions\CreateSnapshotRun;
use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

function ownedSkillsRun(): TrainingRun
{
    return (new CreateSnapshotRun)->handle([
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'speed' => 607,
        'stamina' => 577,
        'power' => 549,
        'guts' => 736,
        'wit' => 397,
    ]);
}

function ownedSkillsSkill(string $name): Skill
{
    return Skill::factory()->create([
        'name' => $name,
        'sp_cost' => 100,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);
}

it('records the eleven owned skills a snapshot run adds on its planner', function (): void {
    $run = ownedSkillsRun();

    // The walk's own list (walk-9 §2 frozen sheet, SR §1.3): ten Details chips plus Corner Recovery ○.
    $names = [
        'Blue Rose Closer', 'Red Shift/LP1211-M', 'Corner Adept ○', 'Swinging Maestro',
        'Nimble Navigator', 'Steadfast', 'Deep Breaths', 'Extra Tank',
        'Pace Chaser Straightaways ○', 'Shrewd Step', 'Corner Recovery ○',
    ];

    $skills = collect($names)->map(fn (string $name): Skill => ownedSkillsSkill($name));

    test()->post(route('runs.skills.sync', $run), [
        'skills' => $skills->map(fn (Skill $skill): array => [
            'skill_id' => $skill->id,
            'status' => SkillAcquisition::Acquired->value,
        ])->all(),
    ])->assertRedirect();

    test()->get(route('runs.skills.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Every skill the run holds lands in the planner's `learned` group when its pivot status is
            // Acquired, which is the status the planner's own add path posts (`SkillsPlanner.vue:255`).
            ->where('groups', fn ($groups): bool => collect($groups)
                ->firstWhere('key', 'learned')['rows'] !== []
                && count(collect($groups)->firstWhere('key', 'learned')['rows']) === 11)
            // A run's own inventory, not a plan: nothing was left in the planned/suggested buckets.
            ->where('groups', fn ($groups): bool => collect($groups)
                ->whereIn('key', ['required', 'recommended'])
                ->flatMap(fn ($group): array => $group['rows'] ?? [])
                ->count() === 0));
});
