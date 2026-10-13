<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\TraineeGoalType;
use App\Models\TrainingRun;
use App\Services\DataPipeline\Contracts\TraineeGoalSourceParser;

/**
 * Flatten the publisher's per-trainee objective ladder into one record per objective.
 *
 * Pure: it sees a document body and returns rows. It resolves nothing against the database — neither
 * the trainee a `char_id` names nor the calendar row a race matches is this class's business, and
 * keeping it that way is what lets the parse be tested without a schema.
 *
 * The document is one object per character: `{char_id, obj_id, objectives[]}`, each objective carrying
 * a condition (`cond_type` selects the kind, `cond_value` the figure), a deadline `turn`, a ladder
 * `order`, an alternate index `race_choice`, and a `races[]` array holding the race row inline.
 * `cond_id` is race-scoped and not requirement-scoped: id 81 is Arima Kinen and appears with
 * `cond_value` 0, 1 and 3 across characters, so the placing lives in `cond_value` alone.
 *
 * Turn arithmetic is the one the career already runs on, and it is confirmed here rather than assumed:
 * `turn = (year - 1) * 24 + (month - 1) * 2 + half`, and a published [Global] client readout of Haru
 * Urara's "Have at least 5000 fans" at "Turn: 37 … Classic Class … Early July" lands on exactly this
 * row (turn 37 = year 2, month 7, Early). `TrainingRun::TURNS_PER_YEAR` owns the constant.
 */
final class GametoraUraObjectivesParser implements TraineeGoalSourceParser
{
    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $body): array
    {
        /** @var mixed $document */
        $document = json_decode($body, true);

        if (! is_array($document)) {
            return [];
        }

        $records = [];

        foreach ($document as $character) {
            if (! is_array($character) || ! isset($character['char_id']) || ! isset($character['objectives'])) {
                continue;
            }

            $charRef = 'gametora:char:'.((int) $character['char_id']);

            foreach ((array) $character['objectives'] as $objective) {
                if (! is_array($objective) || ! isset($objective['turn'], $objective['cond_type'], $objective['cond_value'])) {
                    continue;
                }

                $records[] = $this->record($charRef, $objective);
            }
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $objective
     * @return array<string, mixed>
     */
    private function record(string $charRef, array $objective): array
    {
        $turn = (int) $objective['turn'];
        $condType = (int) $objective['cond_type'];
        $condValue = (int) $objective['cond_value'];
        $targetType = (int) ($objective['target_type'] ?? 0);
        $race = $this->race($objective);

        // The career index the calendar already uses: 24 turns a year, two per month, and the
        // post-December final block sits at 74, 76 and 78 (year 4). Read from TrainingRun so the
        // parser, the catalogue and the Cockpit cannot disagree about what turn 54 means
        // (ADR-0015).
        $careerYear = intdiv($turn - 1, TrainingRun::TURNS_PER_YEAR) + 1;
        $turnInYear = (($turn - 1) % TrainingRun::TURNS_PER_YEAR) + 1;
        $month = intdiv($turnInYear + 1, 2);
        $half = $turnInYear % 2 === 0 ? 'Late' : 'Early';

        return [
            'char_ref' => $charRef,
            'goal_type' => $this->goalType($condType, $condValue, $targetType, $race),
            'required_placement' => $condType === 1 && $targetType === 1 && $race !== null ? $condValue : null,
            'fan_threshold' => $condType === 3 ? $condValue : null,
            'career_year' => $careerYear,
            'turn' => $turn,
            'source_order' => (int) ($objective['order'] ?? 0),
            'source_race_choice' => (int) ($objective['race_choice'] ?? 0),
            'source_cond_id' => (int) ($objective['cond_id'] ?? 0),
            'source_cond_type' => $condType,
            'source_cond_value' => $condValue,
            'source_cond_value_2' => (int) ($objective['cond_value_2'] ?? 0),
            'source_target_type' => $targetType,
            'source_race_type' => (int) ($objective['race_type'] ?? 0),
            'source_race_name' => $race['name_en'] ?? null,
            'global_unreleased' => $this->unreleasedOnGlobal($race),
            'race_slot' => $race === null ? null : [
                'year' => $careerYear,
                'month' => $month,
                'half' => $half,
                'title' => (string) ($race['name_en'] ?? ''),
            ],
        ];
    }

    /**
     * The single race row an objective names, or null when it names none.
     *
     * @param  array<string, mixed>  $objective
     * @return array<string, mixed>|null
     */
    private function race(array $objective): ?array
    {
        $races = $objective['races'] ?? null;

        return is_array($races) && $races !== [] && is_array($races[0]) ? $races[0] : null;
    }

    /**
     * @param  array<string, mixed>|null  $race
     */
    private function goalType(int $condType, int $condValue, int $targetType, ?array $race): string
    {
        if ($targetType === 3) {
            return TraineeGoalType::FinalRound->value;
        }

        if ($condType === 3) {
            return TraineeGoalType::Fan->value;
        }

        if ($condType === 1 && $race !== null) {
            // cv 0 is the client's "no placing asked" form: the debut for every trainee, and the
            // underdog G1 objectives (Haru Urara's Arima Kinen among them).
            return $condValue === 0
                ? TraineeGoalType::Participate->value
                : TraineeGoalType::Placement->value;
        }

        return TraineeGoalType::Undecoded->value;
    }

    /**
     * Whether the source itself says this race has not reached the Global server.
     *
     * @param  array<string, mixed>|null  $race
     */
    private function unreleasedOnGlobal(?array $race): bool
    {
        if ($race === null) {
            return false;
        }

        $servers = $race['unreleased_servers'] ?? null;

        return is_array($servers) && in_array('en', $servers, true);
    }
}
