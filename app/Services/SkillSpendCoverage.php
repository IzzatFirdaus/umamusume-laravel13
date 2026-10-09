<?php

declare(strict_types=1);

namespace App\Services;

/**
 * What the skills still to learn cost against the Skill Points the run has recorded (SCREEN plan
 * D13: "sum SP cost of Required skills against current Skill Points and warn when SP cannot cover
 * them").
 *
 * Every price is the stored base. A missing price is not a zero, so one absent cost refuses the
 * whole sum rather than silently shortening it (D-220), and the caller names the skills it could
 * not price. Pure, so the planner and its tests read the same arithmetic.
 */
final class SkillSpendCoverage
{
    /**
     * @param  array<string, int|null>  $costs  skill name => the stored SP price, null when absent
     * @return array{total_cost: int, remaining: int|null}|null null when any price is absent
     */
    public static function sum(array $costs, ?int $sp): ?array
    {
        $total = 0;

        foreach ($costs as $cost) {
            if ($cost === null) {
                return null;
            }

            $total += $cost;
        }

        return [
            'total_cost' => $total,
            'remaining' => $sp === null ? null : $sp - $total,
        ];
    }
}
