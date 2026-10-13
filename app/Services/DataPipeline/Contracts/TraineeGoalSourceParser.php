<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * A source whose rows are a trainee's career objectives (PRD US-10, ADR-0026).
 *
 * A separate contract for the reason every sibling here exists: the rows carry no display name to
 * cross-reference. Read as ordinary `SourceParser` records, all 1,701 objectives would fail to match
 * an Umamusume and land in `match_candidates` as review rows — a queue holding the whole ladder of
 * every character in the game, filed as characters nobody could identify. Identity comes instead from
 * the source's own `char_id`, resolved through `umamusume.external_ref`, and from the condition id
 * plus deadline turn the row is keyed on.
 *
 * @see RaceCatalogSourceParser The same argument at calendar grain.
 */
interface TraineeGoalSourceParser
{
    /**
     * @return list<array{
     *     char_ref: string,
     *     goal_type: string,
     *     required_placement: int|null,
     *     fan_threshold: int|null,
     *     career_year: int,
     *     turn: int,
     *     source_order: int,
     *     source_race_choice: int,
     *     source_cond_id: int,
     *     source_cond_type: int,
     *     source_cond_value: int,
     *     source_cond_value_2: int,
     *     source_target_type: int,
     *     source_race_type: int,
     *     source_race_name: string|null,
     *     global_unreleased: bool,
     *     race_slot: array{year: int, month: int, half: string, title: string}|null
     * }>
     */
    public function parse(string $body): array;
}
