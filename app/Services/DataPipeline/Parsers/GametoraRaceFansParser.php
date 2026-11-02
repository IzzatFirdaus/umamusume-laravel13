<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\RaceEnrichmentSourceParser;
use JsonException;

final class GametoraRaceFansParser implements RaceEnrichmentSourceParser
{
    /**
     * @return list<array{id: mixed, first: mixed, second: mixed, third: mixed}>
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

            $fans = is_array($row['fans'] ?? null) ? $row['fans'] : [];

            $first = null;
            $second = null;
            $third = null;

            foreach ($fans as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $order = $entry['order'] ?? null;
                $value = $entry['fans'] ?? null;

                if ($order === 1 || $order === '1') {
                    $first = $value;
                } elseif ($order === 2 || $order === '2') {
                    $second = $value;
                } elseif ($order === 3 || $order === '3') {
                    $third = $value;
                }
            }

            $records[] = [
                'id' => $row['id'] ?? null,
                'first' => $first,
                'second' => $second,
                'third' => $third,
            ];
        }

        return $records;
    }
}
