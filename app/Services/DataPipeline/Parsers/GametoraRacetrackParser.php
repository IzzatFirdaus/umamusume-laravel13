<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\RaceEnrichmentSourceParser;
use JsonException;

final class GametoraRacetrackParser implements RaceEnrichmentSourceParser
{
    /**
     * @return list<array{id: mixed, name_en: mixed, name_ja: mixed, country: mixed}>
     */
    public function parse(string $body): array
    {
        try {
            $tracks = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($tracks)) {
            return [];
        }

        $records = [];

        foreach ($tracks as $track) {
            if (! is_array($track) || ! array_key_exists('id', $track)) {
                continue;
            }

            $records[] = [
                'id' => $track['id'],
                'name_en' => $track['name_en'] ?? null,
                'name_ja' => $track['name_ja'] ?? null,
                'country' => $track['country'] ?? null,
            ];
        }

        return $records;
    }
}
