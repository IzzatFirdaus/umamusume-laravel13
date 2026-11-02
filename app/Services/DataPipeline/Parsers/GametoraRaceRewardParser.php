<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\RaceEnrichmentSourceParser;
use JsonException;

final class GametoraRaceRewardParser implements RaceEnrichmentSourceParser
{
    /**
     * @return list<array{id: mixed, rewards: list<array{item: mixed, num_min: mixed, num_max: mixed, chance: mixed}>}>
     */
    public function parse(string $body): array
    {
        try {
            $rows = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($rows)) {
            return [];
        }

        $records = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rewards = [];

            foreach ((is_array($row['rewards_combined'] ?? null) ? $row['rewards_combined'] : []) as $reward) {
                if (! is_array($reward)) {
                    continue;
                }

                $rewards[] = [
                    'item' => $reward['item'] ?? null,
                    'num_min' => $reward['num_min'] ?? null,
                    'num_max' => $reward['num_max'] ?? null,
                    'chance' => $reward['chance'] ?? null,
                ];
            }

            $records[] = [
                'id' => $row['id'] ?? null,
                'rewards' => $rewards,
            ];
        }

        return $records;
    }
}
