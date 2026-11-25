<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Scenario\FinaleReader;

final class VeteranPortfolio
{
    public static function availableFor(TrainingRun $run): bool
    {
        return in_array($run->status, [RunStatus::Active, RunStatus::Completed], true)
            && $run->umamusume->slug !== 'rice-shower';
    }

    /**
     * @param  array{reached: bool, outcome: string|null, placement: string|null, turn: int|null, label: string}|null  $finale
     * @return array<string, mixed>|null
     */
    public static function forRun(TrainingRun $run, ?TurnEntry $latest, ?array $finale = null): ?array
    {
        if (! self::availableFor($run)) {
            return null;
        }

        $run->loadMissing(['umamusume', 'turnEntries', 'raceEntries.raceCatalogSlot', 'raceEntries.turnEntry', 'skills']);
        $finale ??= FinaleReader::forRun($run);

        $strengths = [];
        foreach (config('scenarios.stat_order') as $stat) {
            $value = $latest?->statValue($stat);
            if ($value !== null) {
                $strengths[] = ['label' => $stat, 'value' => $value];
            }
        }
        $currentStats = $run->status === RunStatus::Active ? $strengths : [];
        usort($strengths, fn (array $a, array $b): int => $b['value'] <=> $a['value']);

        $axes = collect($run->umamusume->aptitudeAxes());
        $distanceLabels = [1 => 'Sprint', 2 => 'Mile', 3 => 'Medium', 4 => 'Long'];
        $skillDistances = $run->skills
            ->filter(fn (Skill $skill): bool => $skill->pivot->status === SkillAcquisition::Acquired->value)
            ->pluck('distance_type')->filter()->unique()->sort()->values()
            ->map(fn (int $distance): ?string => $distanceLabels[$distance] ?? null)
            ->filter()->values()->all();

        return [
            'strengths' => array_slice($strengths, 0, 2),
            'current_stats' => $currentStats,
            'snapshot_turn' => $latest?->turn,
            'fit' => [
                'surfaces' => $axes->slice(0, 2)->whereIn('letter', ['A', 'S'])->pluck('label')->values()->all(),
                'distances' => $axes->slice(2, 4)->whereIn('letter', ['A', 'S'])->pluck('label')->values()->all(),
                'styles' => $axes->slice(6)->whereIn('letter', ['A', 'S'])->pluck('label')->values()->all(),
                'skill_distances' => $skillDistances,
            ],
            'progression_inferred' => $run->status === RunStatus::Completed && self::impliesEarlierRounds($run, $finale),
            'turns' => $run->liveTurnEntries()->count(),
            'races' => $run->raceEntries->where('status', RaceEntryStatus::Completed)->count(),
            'events' => $run->turnEvents()->count(),
            'deck_slots' => $run->deckSlots()->count(),
            'legacy_recorded' => $run->legacySelection() !== null
                || $run->inheritance_parent_a_id !== null
                || $run->inheritance_parent_b_id !== null,
            'deck_url' => route('runs.deck', $run),
            'inheritance_url' => route('runs.inheritance', $run),
            'timeline_url' => route('runs.timeline', $run),
            'source' => $run->import_source,
        ];
    }

    /**
     * The URA guide describes win-gated preliminaries. A known Final placing implies progression
     * through unlogged rounds, provided the stored preliminary results contain no contrary evidence.
     *
     * @param  array{reached: bool, outcome: string|null, placement: string|null, turn: int|null, label: string}|null  $finale
     */
    private static function impliesEarlierRounds(TrainingRun $run, ?array $finale): bool
    {
        if ($run->scenarioKey() !== 'ura_finale'
            || ($finale['outcome'] ?? null) !== RaceEntryStatus::Completed->label()
            || $finale['placement'] === null) {
            return false;
        }

        $preliminaries = $run->raceEntries->filter(fn (RaceEntry $entry): bool => $entry->raceCatalogSlot?->year === RaceCatalogSlot::YEAR_FINALE
            && $entry->raceCatalogSlot->scenario_key === null
            && in_array($entry->raceCatalogSlot->half, ['Early', 'Late'], true));

        return $preliminaries->pluck('raceCatalogSlot.half')->unique()->count() < 2
            && $preliminaries->every(fn (RaceEntry $entry): bool => $entry->status === RaceEntryStatus::Completed
                && ($entry->placement === null || $entry->placement === 1));
    }
}
