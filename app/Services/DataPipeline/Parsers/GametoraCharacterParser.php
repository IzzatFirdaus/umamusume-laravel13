<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\ReleaseStatus;
use App\Services\DataPipeline\Contracts\SourceParser;
use JsonException;

/**
 * Reads the GameTora character-card dataset and emits one catalog record per
 * Umamusume (PRD FR-A-1, FR-A-5, FR-B-2).
 *
 * The dataset holds one entry per costume card, so several entries share a
 * char_id. Debut form wins: the record with the earliest `release` supplies the
 * dates, because the catalog models the character rather than the collectable
 * card. Costume variants are therefore dropped, not merged.
 */
final class GametoraCharacterParser implements SourceParser
{
    /** Sorts a card with no JP date last without breaking string comparison. */
    private const UNKNOWN_DATE = '9999-12-31';

    /**
     * Aptitude columns in the export's own element order: surface, then the four
     * distance bands, then the four running styles. That order was confirmed cell
     * by cell against two independent publishers before any of it was stored.
     */
    public const APTITUDE_COLUMNS = [
        'aptitude_turf',
        'aptitude_dirt',
        'aptitude_sprint',
        'aptitude_mile',
        'aptitude_medium',
        'aptitude_long',
        'aptitude_front_runner',
        'aptitude_pace_chaser',
        'aptitude_late_surger',
        'aptitude_end_closer',
    ];

    /**
     * @return list<array<string, string|null>> catalog fields, plus the ten aptitude
     *                                          letters only when a card carries a complete and well formed set
     */
    public function parse(string $body): array
    {
        try {
            $cards = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($cards)) {
            return [];
        }

        $debutForms = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $name = isset($card['name_en']) ? trim((string) $card['name_en']) : '';
            $charId = $card['char_id'] ?? null;

            if ($name === '' || ! is_numeric($charId)) {
                continue;
            }

            $charId = (int) $charId;
            $releasedAt = isset($card['release']) ? (string) $card['release'] : self::UNKNOWN_DATE;
            $incumbent = $debutForms[$charId] ?? null;

            if ($incumbent === null || $releasedAt < $incumbent['release']) {
                $debutForms[$charId] = $card + ['release' => $releasedAt];
            }
        }

        ksort($debutForms);

        $records = [];

        foreach ($debutForms as $charId => $card) {
            $globalDebut = $this->dateOrNull($card['release_en'] ?? null);
            $jpDebut = $this->dateOrNull($card['release'] ?? null);

            $records[] = [
                'name' => trim((string) $card['name_en']),
                // GameTora publishes `name_jp`; `name_ja` is this app's own column name.
                'name_ja' => $this->textOrNull($card['name_jp'] ?? null),
                // A costume card can exist on [Global] while no debut form does; the
                // debut date is what the catalog means by "released here".
                'release_status' => $globalDebut === null
                    ? ReleaseStatus::JapanOnly->value
                    : ReleaseStatus::GlobalReleased->value,
                'jp_debut_date' => $jpDebut === self::UNKNOWN_DATE ? null : $jpDebut,
                'global_debut_date' => $globalDebut,
                'external_ref' => 'gametora:char:'.$charId,
                ...$this->aptitudes($card['aptitude'] ?? null),
            ];
        }

        return $records;
    }

    /**
     * @return array<string, string> empty unless all ten letters are present
     */
    private function aptitudes(mixed $aptitude): array
    {
        if (! is_array($aptitude) || count($aptitude) !== count(self::APTITUDE_COLUMNS)) {
            return [];
        }

        $columns = [];

        foreach (self::APTITUDE_COLUMNS as $index => $column) {
            $letter = $aptitude[$index] ?? null;

            if (! is_string($letter) || preg_match('/^[SABCDEFG]$/', $letter) !== 1) {
                return [];
            }

            $columns[$column] = $letter;
        }

        return $columns;
    }

    private function dateOrNull(mixed $value): ?string
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value)) !== 1) {
            return null;
        }

        return trim($value);
    }

    private function textOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
