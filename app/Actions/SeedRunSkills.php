<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;

/**
 * Seed a new run's starting skills from the character card it was started on (KI-33, `S-A-P01.2`).
 *
 * **One owner, two doors.** A run is created by the wizard (`PreflightController::store()`) or by the
 * dashboard's run form (`TrainingRunController::store()`). Only the second seeded, so the same trainee
 * produced a Skills section that depended on which door the Trainer walked through. Both call this now.
 *
 * **`Suggested`, never `Acquired`.** A seeded row is the card's implication, not a skill the Trainer
 * earned: the status is what keeps the two distinguishable on the planner, and it is what makes the
 * seeding reversible by the Trainer rather than a claim about their career (`P-01` §7 decision 2).
 * `setSkillStatus` upserts through `syncWithoutDetaching`, so seeding is idempotent and a skill the
 * Trainer already recorded keeps the state they gave it.
 *
 * **A run that names no card has nothing to seed from**, and a card whose lists are null was written by
 * a source that published none. Both are real states and both return without writing.
 */
final class SeedRunSkills
{
    public function handle(TrainingRun $run): void
    {
        $card = $run->characterCard;

        if ($card === null) {
            return;
        }

        // The union, deduped: an id should not appear on both lists, and if the source ever says so the
        // run gets one row rather than two fighting over the same pivot key.
        $exportIds = array_values(array_unique([
            ...($card->skills_innate ?? []),
            ...($card->skills_unique ?? []),
        ]));

        if ($exportIds === []) {
            return;
        }

        foreach (Skill::query()->availableOnGlobal()->whereIn('export_id', $exportIds)->get() as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }
    }
}
