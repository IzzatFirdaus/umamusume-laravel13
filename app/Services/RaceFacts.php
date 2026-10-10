<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RaceCatalogSlot;

/**
 * The brief's field list for one race, present or absent, each absence carrying its reason.
 *
 * One owner, because two screens print it: the Race Decision (`SCR-CAR-013`, D10) and the Scenario
 * Race Planner (`SCR-CAR-023`, E5). Built server-side rather than in a component, so the reason a
 * figure is missing travels with the figure and the wording lives in one place
 * (`LegacyController`'s Spark-chance cell made the same call).
 *
 * Four of the ten fields have no column behind them, and one is held on `ADR-0016`. The reason
 * strings are the refusal, so they are asserted rather than paraphrased.
 */
final class RaceFacts
{
    /**
     * @return list<array{key: string, label: string, value: string|null, title: string|null}>
     */
    public static function forSlot(RaceCatalogSlot $slot): array
    {
        return [
            self::fact('grade', 'Grade', $slot->tier, 'This catalogue row carries no tier label.'),
            self::fact(
                'distance',
                'Distance',
                $slot->distance === null ? null : $slot->distanceLabel(),
                'This catalogue row carries no distance, so the client decides it from the trainee\'s most-run types.',
            ),
            self::fact(
                'distance_band',
                'Distance band',
                $slot->distance_band,
                'This catalogue row carries no distance band.',
                $slot->distance_band === null ? null : 'Band names are the export\'s own. The corpus disagrees with itself at the 1400 m line (Game8 calls it Sprint, the export calls it Mile), so the band is printed as the export states it and no band is derived from a metre count.',
            ),
            self::fact('surface', 'Surface', $slot->surface, 'This catalogue row carries no surface.'),
            self::fact(
                'running_style',
                'Running style',
                null,
                'A running style is the trainee\'s aptitude, not a property of the race, and no column here holds a per-race one. The trainee\'s own style letters are on her profile.',
            ),
            self::fact(
                'fan_gain',
                'Fan gain',
                $slot->fanPayout(),
                'Payout not recorded for this race. This row carries the payout curve id the export publishes'
                    .($slot->fans_gain_curve === null ? ' (none on this row)' : " ({$slot->fans_gain_curve})")
                    .', and no per-placement payout is stored against it.',
            ),
            self::fact('reward', 'Reward', null, 'No column holds a race reward, and no source in this repository states one per race.'),
            self::fact('skill_points', 'Skill Points', null, 'No column holds a skill-point payout. The Trainer records what the client paid with the turn, on Training.'),
            self::fact('scenario_reward', 'Scenario reward', null, 'No column holds a scenario reward, and the scenario panels that would state one are not built.'),
            self::fact(
                'win_probability',
                'Estimated win probability',
                null,
                'Held: race prediction is blocked on the requirement data `ADR-0016` measures, which is an open question and not a permission. `PRD.md` §6.11 stands unamended, so this tool computes and prints no win figure.',
            ),
        ];
    }

    /**
     * Whether this row's own columns describe the race, as opposed to naming it.
     *
     * R2-12. The seeded calendar carries placeholder rows — the debut, the Junior maidens, the finals
     * qualifiers — whose distance, band and surface are null, and every card they appear on then prints
     * ten labelled refusals. Five of those ten are refused by design for every race (running style is
     * the trainee's, three payouts have no column, and the win figure is held on `ADR-0016`), so a row
     * with none of the four course figures has nothing the catalogue said about it. The caller folds
     * those cards to one line; this is the test for that, kept beside the fields it reads.
     *
     * A tier is deliberately not a course figure: `Junior Make Debut` carries the tier word `Debut` and
     * is exactly the row the audit called a wall.
     */
    public static function describesRace(RaceCatalogSlot $slot): bool
    {
        return $slot->distance !== null
            || $slot->distance_band !== null
            || $slot->surface !== null
            || $slot->hasFanGate()
            || $slot->fanPayout() !== null;
    }

    /**
     * @return array{key: string, label: string, value: string|null, title: string|null}
     */
    private static function fact(string $key, string $label, ?string $value, string $absentTitle, ?string $presentTitle = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'title' => $value === null ? $absentTitle : $presentTitle,
        ];
    }
}
