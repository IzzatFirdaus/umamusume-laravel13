<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\ScenarioSourceParser;
use Illuminate\Support\Carbon;
use JsonException;

/**
 * Reads the GameTora scenario dataset into training-scenario reference rows
 * (PRD FR-A-5, ADR-0004).
 *
 * The dataset publishes no cap column. It carries `stats`, a per-stat bonus over
 * the base cap, and `hard_caps`, the database ceiling above the in-run cap. The
 * base is a game constant: `1200 + stats` reproduces the four Global scenario cap
 * rows printed in the reference document figure for figure, which is why the
 * formula rather than prose guides is trusted here (ADR-0002 second amendment).
 */
final class GametoraScenarioParser implements ScenarioSourceParser
{
    /** In-run cap per stat before a scenario's own bonus is applied. */
    public const BASE_STAT_CAP = 1200;

    /** Date [Global] raised the caps of scenarios already live there. */
    public const GLOBAL_CAP_REWORK_DATE = '2026-07-01';

    /**
     * @return list<array{slug: string, name: string, name_ja?: string|null, order?: int|null,
     *                   jp_start_date?: string|null, global_start_date?: string|null,
     *                   cap_speed: int, cap_stamina: int, cap_power: int, cap_guts: int, cap_wit: int,
     *                   hard_cap?: int|null, caps_reworked_at?: string|null, external_ref?: string|null}>
     */
    public function parse(string $body): array
    {
        try {
            $scenarios = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($scenarios)) {
            return [];
        }

        $records = [];

        foreach ($scenarios as $scenario) {
            if (! is_array($scenario)) {
                continue;
            }

            $name = $this->text($scenario['name_en'] ?? null) ?? $this->text($scenario['name_en_old'] ?? null);
            $slug = $this->text($scenario['url_name'] ?? null);
            $bonus = $scenario['stats'] ?? null;

            if ($name === null || $slug === null || ! is_array($bonus) || count($bonus) !== 5) {
                continue;
            }

            $caps = [];

            foreach ($bonus as $offset) {
                $caps[] = self::BASE_STAT_CAP + (int) $offset;
            }

            $jpStart = $this->date($scenario['start_ja'] ?? null, 'Asia/Tokyo');
            $globalStart = $this->date($scenario['start_en'] ?? null);

            $records[] = [
                'slug' => $slug,
                'name' => $name,
                'name_ja' => $this->text($scenario['name_ja'] ?? null),
                'order' => isset($scenario['order']) ? (int) $scenario['order'] : null,
                'jp_start_date' => $jpStart,
                'global_start_date' => $globalStart,
                'cap_speed' => $caps[0],
                'cap_stamina' => $caps[1],
                'cap_power' => $caps[2],
                'cap_guts' => $caps[3],
                'cap_wit' => $caps[4],
                'hard_cap' => isset($scenario['hard_caps'][0]) ? (int) $scenario['hard_caps'][0] : null,
                // Only a scenario already live before the rework had its caps raised
                // by it; a later launch ships with the higher table.
                'caps_reworked_at' => $this->reworkedBefore($globalStart),
                'external_ref' => 'gametora:scenario:'.($scenario['id'] ?? $slug),
            ];
        }

        return $records;
    }

    private function reworkedBefore(?string $globalStart): ?string
    {
        if ($globalStart === null || $globalStart >= self::GLOBAL_CAP_REWORK_DATE) {
            return null;
        }

        return self::GLOBAL_CAP_REWORK_DATE;
    }

    private function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Epochs are absolute; the zone decides which calendar date the source meant.
     * [JP] announcements carry Asia/Tokyo, [Global] carries UTC (PRD FR-B-6).
     */
    private function date(mixed $epoch, string $timezone = 'UTC'): ?string
    {
        if (! is_numeric($epoch)) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $epoch, $timezone)->toDateString();
    }
}
