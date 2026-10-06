<?php

declare(strict_types=1);

namespace App\Services\Legacy;

use App\Models\TrainingRun;
use App\Models\Veteran;

/**
 * One Veteran as a list row, in the shape the surfaces that list the library already print.
 *
 * `LegacyController::index()` (the Legacy Lab browse list) and `VeteranController::index()` (the Veteran
 * library) show the same record: the trainee she ran as, the scenario she ran it in, the state the run is
 * in, the Trainer's own tags and notes, and whether a Legacy read-back exists on it. Two copies of that
 * mapping would be two shapes for one row, and the first screen to gain a column would silently print a
 * different Veteran than the other does, so the shape has one owner here.
 *
 * **It reads; it derives nothing** (PRD FR-G-4, `ADR-0020` §3). No factor analysis, no spark count, no
 * legacy value, no ranking: those are the held computations, and a row that carried one would put a score
 * on a screen whose contract says the library stores and searches. Everything here is a stored column or
 * a route.
 *
 * Both link fields are always present. The Legacy Lab renders `builder_url` and ignores `veteran_url`;
 * the library renders both. A key one screen does not use is a wider row; two rows that disagree about
 * what a Veteran is would be the defect.
 */
final class VeteranRow
{
    /**
     * @return array{id: int, run_id: int, trainee: string, trainee_ja: string|null, scenario_label: string,
     *               status_label: string, tags: list<string>, notes: string|null,
     *               hasSelection: bool, builder_url: string|null, veteran_url: string}
     */
    public static function from(Veteran $veteran): array
    {
        // The caller eager-loads `trainingRun.umamusume` (`ListVeterans::handle()`), so this is a read of
        // rows already in memory rather than a query per row.
        $run = $veteran->trainingRun;
        $payload = $run->legacySelection();

        return [
            'id' => $veteran->id,
            // The run's own id, because the Legacy Lab's compare surface takes it: a Veteran is a pointer
            // and the comparison is between runs, not between library rows (`Legacy/Index.vue`).
            'run_id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => self::scenarioLabel($run),
            'status_label' => $run->status->label(),
            'tags' => $veteran->tags ?? [],
            'notes' => $veteran->notes,
            'hasSelection' => $payload !== null,
            'builder_url' => $payload === null ? null : route('legacy.builder', $run),
            'veteran_url' => route('veterans.show', $veteran),
        ];
    }

    /**
     * The scenario's displayed label, from `config/scenarios.php` and nowhere else.
     *
     * A run with no scenario is a real state, and the client's own word for the absence is the sentence
     * this returns rather than an empty string a row would then render as a gap.
     */
    public static function scenarioLabel(TrainingRun $run): string
    {
        if ($run->scenario === null) {
            return 'No scenario set';
        }

        /** @var array<string, array{label: string}> $scenarios */
        $scenarios = config('scenarios.scenarios');

        return $scenarios[$run->scenario]['label'] ?? $run->scenario;
    }
}
