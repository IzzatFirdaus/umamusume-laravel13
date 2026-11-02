<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\RaceEnrichmentSourceParser;
use JsonException;

final class GametoraRaceParser implements RaceEnrichmentSourceParser
{
    /**
     * @return list<array{id: mixed, name_en: mixed, name_ja: mixed, course: mixed, direction: mixed, season: mixed, entries: mixed, factor_effect_1: mixed, factor_effect_2: mixed, track_id: mixed}>
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

            $factor = is_array($row['factor'] ?? null) ? $row['factor'] : [];

            $records[] = [
                'id' => $row['id'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'name_ja' => $row['name_ja'] ?? null,
                'course' => $row['course'] ?? null,
                'direction' => $row['direction'] ?? null,
                'season' => $row['season'] ?? null,
                'entries' => $row['entries'] ?? null,
                'factor_effect_1' => $factor['effect_1'] ?? null,
                'factor_effect_2' => $factor['effect_2'] ?? null,
                'track_id' => $row['track'] ?? null,
            ];
        }

        return $records;
    }
}
