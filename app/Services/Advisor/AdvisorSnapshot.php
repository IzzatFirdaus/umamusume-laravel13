<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Enums\EnergyState;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/**
 * One consistent, versioned reading of a career, plus the fingerprint that makes a stale answer
 * detectable (Advisor AI slice, Phase B).
 *
 * **It reads; it never computes a game fact.** Every number here is either a value the Trainer
 * entered on a turn, a value the run row stores, or `null` with the reason it is null. Nothing is
 * inferred from another column, because the failure this class exists to prevent is a model being
 * handed a figure no source supports: a missing Energy reading is *not* zero, a missing race result
 * is *not* a loss, and an unrecorded Spirit Burst count is *not* zero bursts.
 *
 * **States are named, not collapsed.** The three-value `EnergyState` ('exact' / 'band' / 'unknown')
 * is the repository's own vocabulary and is carried through unchanged rather than reduced to a
 * number-plus-flag. `unknown` on this axis is a reading the Trainer took, not a blank the tool
 * failed to fill (`app/Enums/EnergyState.php`).
 *
 * **The revision fingerprint is derived, never stored.** It hashes the fields that materially
 * change a recommendation, so logging a turn invalidates a cached answer by construction rather
 * than by a timeout. No migration is needed, which is why there is none: `AGENTS.md` §11 asks for
 * a migration to ship a digest and an ADR, and a fingerprint over existing columns needs neither.
 */
final readonly class AdvisorSnapshot
{
    /**
     * @param  array<string, int|null>  $stats  keyed by the stat matrix's names
     * @param  array<string, mixed>  $resources  scenario resources the run actually stores
     * @param  list<string>  $unknowns  human-readable names of facts this snapshot does not hold
     */
    public function __construct(
        public int $careerId,
        public string $revision,
        public int $turn,
        public string $scenario,
        public string $scenarioLabel,
        public array $stats,
        public ?int $energy,
        public string $energyState,
        public ?string $energyBand,
        public ?string $mood,
        public ?int $skillPoints,
        public ?int $fans,
        public array $resources,
        public array $unknowns,
        public bool $finished,
    ) {}

    /**
     * Reads one career into a snapshot.
     *
     * `$latest` is passed in rather than queried here so the caller keeps ownership of "the previous
     * turn" — `TrainingRunController::previousTurn()` already holds that rule, including a turn
     * inserted in the middle of a run, and a second implementation of it would be a second answer
     * to the same question.
     */
    public static function forRun(TrainingRun $run, ?TurnEntry $latest): self
    {
        /** @var list<string> $statOrder */
        $statOrder = config('scenarios.stat_order');

        $stats = [];
        $unknowns = [];

        foreach ($statOrder as $stat) {
            $column = strtolower($stat);
            $value = $latest?->getAttribute($column);
            $stats[$stat] = is_numeric($value) ? (int) $value : null;

            if ($stats[$stat] === null) {
                $unknowns[] = $stat;
            }
        }

        // A run that has logged no turn has no reading of any kind. Handled once here rather than
        // scattered through the arms below: if `energy_state` is `Exact`, `$latest` cannot be null,
        // because the state was read off the same row.
        if ($latest === null) {
            return new self(
                careerId: $run->id,
                revision: self::fingerprint($run, null),
                turn: $run->nextTurnNumber(),
                scenario: $run->scenarioKey(),
                scenarioLabel: (string) config("scenarios.scenarios.{$run->scenarioKey()}.label"),
                stats: array_fill_keys($statOrder, null),
                energy: null,
                energyState: 'unknown',
                energyBand: null,
                mood: null,
                skillPoints: null,
                fans: null,
                resources: self::resources($run),
                unknowns: [...$statOrder, 'Energy', 'Mood', 'every turn reading'],
                finished: $run->status !== RunStatus::Active,
            );
        }

        // The three-value enum is the whole contract: an `Unknown` row outranks a stale figure
        // still sitting on the column, and a `Band` row has no exact number by design.
        $energy = match ($latest->energy_state) {
            EnergyState::Exact => $latest->energy,
            EnergyState::Band, EnergyState::Unknown => null,
            null => $latest->energy,
        };

        if ($energy === null) {
            $unknowns[] = 'Energy';
        }

        if ($latest->mood === null) {
            $unknowns[] = 'Mood';
        }

        return new self(
            careerId: $run->id,
            revision: self::fingerprint($run, $latest),
            turn: $run->nextTurnNumber(),
            scenario: $run->scenarioKey(),
            scenarioLabel: (string) config("scenarios.scenarios.{$run->scenarioKey()}.label"),
            stats: $stats,
            energy: $energy,
            energyState: $latest->energy_state->value ?? 'unknown',
            energyBand: $latest->energy_band,
            mood: $latest->mood?->value,
            skillPoints: is_numeric($latest->sp) ? (int) $latest->sp : null,
            fans: is_numeric($latest->fans) ? (int) $latest->fans : null,
            resources: self::resources($run),
            unknowns: array_values(array_unique($unknowns)),
            finished: $run->status !== RunStatus::Active,
        );
    }

    /**
     * The fingerprint a cached answer is keyed on, and the value a response carries so a stale one
     * can be recognised.
     *
     * It hashes the turn number and the reading the recommendation actually depends on. A turn
     * logged, corrected or superseded changes the hash; a note edited on the run does not, because a
     * note changes nothing the advisor ranks on and invalidating on it would be cache churn wearing
     * the word "safety".
     */
    private static function fingerprint(TrainingRun $run, ?TurnEntry $latest): string
    {
        $material = [
            'run' => $run->id,
            'scenario' => $run->scenarioKey(),
            'next_turn' => $run->nextTurnNumber(),
            'status' => $run->status->value,
            'target' => $run->build_target,
            'latest' => $latest === null ? null : [
                'id' => $latest->id,
                'turn' => $latest->turn,
                'energy' => $latest->energy,
                'energy_state' => $latest->energy_state?->value,
                'energy_band' => $latest->energy_band,
                'mood' => $latest->mood?->value,
                'speed' => $latest->speed,
                'stamina' => $latest->stamina,
                'power' => $latest->power,
                'guts' => $latest->guts,
                'wit' => $latest->wit,
                'sp' => $latest->sp,
                'fans' => $latest->fans,
            ],
        ];

        return substr(hash('sha256', (string) json_encode($material)), 0, 16);
    }

    /**
     * The scenario resources the run row stores, keyed as the model holds them.
     *
     * Only stored columns appear. A scenario whose counters this tool does not record contributes
     * nothing rather than a zero, for the same reason the stats above carry nulls: a zero would be
     * a recorded reading the Trainer never took.
     *
     * @return array<string, mixed>
     */
    private static function resources(TrainingRun $run): array
    {
        $keys = [
            'unity_trainings_count',
            'spirit_bursts_count',
            'extreme_bursts_count',
            'current_spirit_proxy',
            'team_rank',
            'team_preseason_wins',
            'team_league_placement',
            'shop_resets_in',
            'current_objective_index',
        ];

        $resources = [];

        foreach ($keys as $key) {
            $value = $run->getAttribute($key);

            if ($value !== null) {
                $resources[$key] = $value;
            }
        }

        return $resources;
    }

    /**
     * The state a provider call is allowed to see, and nothing else.
     *
     * Deliberately narrow: the run id is included because the response is validated against it, and
     * no name, note, team motto or imported-source string is, because none of them can change the
     * ranking and every one of them is Trainer-authored text leaving the machine
     * (`config/advisor_ai.php`).
     *
     * @return array<string, mixed>
     */
    public function forProvider(): array
    {
        return [
            'career_id' => $this->careerId,
            'revision' => $this->revision,
            'turn' => $this->turn,
            'scenario' => $this->scenarioLabel,
            'stats' => array_filter($this->stats, static fn (?int $v): bool => $v !== null),
            'energy' => $this->energy,
            'energy_state' => $this->energyState,
            'energy_band' => $this->energyBand,
            'mood' => $this->mood,
            'skill_points' => $this->skillPoints,
            'fans' => $this->fans,
            'scenario_resources' => $this->resources,
            'unknowns' => $this->unknowns,
        ];
    }
}
