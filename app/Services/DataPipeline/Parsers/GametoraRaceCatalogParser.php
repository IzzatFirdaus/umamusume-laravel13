<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\RaceCatalogSourceParser;
use JsonException;

/**
 * Reads the GameTora career race-instance dataset into `race_catalog_slots` rows
 * (ADR-0003 Amendment R3: reference data arrives through the fetch engine).
 *
 * Every decoding here is the one recorded in
 * `docs/scenarios/09-global-race-calendar.md`, which is corroborated against
 * `[Global]` client captures; this class re-states none of that evidence and
 * points at it instead.
 *
 * Sentinels are dropped, not stored. The export writes `99999` for a race whose
 * distance and surface the game decides from the Trainer's most-run types, and
 * `0` for no fan gate is a different claim from "not stated" — so a sentinel
 * becomes `null`, and a real zero would survive as a zero.
 */
final class GametoraRaceCatalogParser implements RaceCatalogSourceParser
{
    /** Export sentinel: the value is decided at runtime, not fixed by the dataset. */
    public const UNRESOLVED = 99999;

    /**
     * Grade code to `[Global]` tier label.
     *
     * Evidence chain, in full, at docs/scenarios/09-global-race-calendar.md
     * §"Tier labels, and how each one was pinned". Short form, because this map is
     * contested: `races.json` carries the numeric code and no label field, which is
     * true of that file and is not the same as there being no label source.
     *
     *   100 G1    client copy, "G1 Averseness" in skills.json id 200311
     *   200 G2    uma.guide gradeName + Game8's per-race tier, two publishers
     *   300 G3    same two; uma.guide's row count at 300 equals this export's 76
     *   400 OP    three client names containing 「オープン」, plus uma.guide
     *   700 Pre-OP  uma.guide ONLY. Game8's table is graded-only and has no
     *               Pre-OP row, so its silence is not agreement. Single-domain.
     *
     * What pins 200 and 300 rather than merely suggesting them is the 12-cell test:
     * unique races per distance band per code match uma.guide's published
     * distribution on all twelve cells, and a swapped 200/300 mapping fails every
     * row. That is arithmetic across two different artifacts, not a label lookup.
     *
     * ScenarioSlotSeeder nulls 200/300/700 per R65, on the narrower reading that
     * the export alone is the only admissible source. That disagreement is
     * deliberate and unreconciled; see docs/design-research/RACE-CALENDAR-GAPS.md.
     */
    private const TIER_BY_GRADE = [
        100 => 'G1',
        200 => 'G2',
        300 => 'G3',
        400 => 'OP',
        700 => 'Pre-OP',
        800 => 'Maiden',
        900 => 'Debut',
    ];

    /** Terrain code to surface. 2 is Dirt, confirmed against the February Stakes row. */
    private const SURFACE_BY_TERRAIN = [1 => 'Turf', 2 => 'Dirt'];

    /**
     * The four `[Global]` scenario finals, keyed by the export's slot id.
     *
     * `09` §"Scenario differences" establishes that the scenarios share every
     * monthly slot and differ only here, so `scenario_key` is null everywhere
     * except on these rows. `final_masters` is deliberately absent: Grand Masters
     * carries no `start_en` and is `[JP-Only]` (`docs/scenarios/08`).
     */
    private const GLOBAL_FINALS_BY_SLOT = [
        'final' => 'ura_finale',
        'final_aoharu' => 'unity_cup',
        'final_mant' => 'trackblazer',
        'final_live' => 'our_grand_concert',
    ];

    /**
     * Slots the game will not let a career pass without. This is scenario-scoped
     * and is *not* the per-character Goal: ADR-0003 R3 and `09` both keep those
     * ideas apart, and the debut is mandatory for every trainee while carrying no
     * Goal pennant unless a character's own objectives name it.
     */
    private const MANDATORY_SLOTS = [
        'debut', 'qual', 'semi',
        'final', 'final_aoharu', 'final_mant', 'final_live',
    ];

    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $body): array
    {
        try {
            $instances = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($instances)) {
            return [];
        }

        $rows = [];

        foreach ($instances as $instance) {
            $row = $this->row($instance);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $instance  mixed, not array: a body that decodes to a map yields
     *                           scalar values here, and a fetch must not throw over it
     * @return array<string, mixed>|null null when the row is not `[Global]` career content
     */
    private function row(mixed $instance): ?array
    {
        if (! is_array($instance)) {
            return null;
        }

        $details = $instance['details'] ?? null;
        $slotId = $instance['id'] ?? null;

        if (! is_array($details) || ! is_string($slotId) || ! is_int($instance['year'] ?? null)) {
            return null;
        }

        $year = (int) $instance['year'];

        if ($year < 1 || $year > 4) {
            return null;
        }

        // Global only: the export's own server flag, not a translation of one.
        if ($this->unreleasedOnGlobal($details)) {
            return null;
        }

        if (! $this->isGlobalCareerSlot($slotId, $year)) {
            return null;
        }

        $month = $this->intOrNull($instance['month'] ?? null, 1, 12);
        $half = (int) ($instance['half'] ?? 0);
        $grade = (int) ($details['grade'] ?? 0);

        return [
            'scenario_key' => self::GLOBAL_FINALS_BY_SLOT[$slotId] ?? null,
            'year' => $year,
            'month' => $month,
            'half' => match ($half) {
                1 => 'Early',
                2 => 'Late',
                default => null,
            },
            'turn' => $month === null ? null : (($month - 1) * 2) + ($half === 2 ? 2 : 1),
            'slot_label' => $this->label($month, $half),
            'title' => (string) ($details['name_en'] ?? ''),
            'tier' => self::TIER_BY_GRADE[$grade] ?? null,
            'grade_code' => $grade,
            'distance' => $this->intOrNull($details['distance'] ?? null, 1, 20000),
            'distance_band' => $this->band($details['distance'] ?? null),
            'surface' => self::SURFACE_BY_TERRAIN[(int) ($details['terrain'] ?? 0)] ?? null,
            'track_id' => $this->intOrNull($details['track'] ?? null, 1, PHP_INT_MAX),
            'race_id' => $this->intOrNull($details['id'] ?? null, 1, PHP_INT_MAX),
            'fans_needed' => $this->intOrNull($instance['fans_needed'] ?? null, 0, PHP_INT_MAX),
            'fans_gain_curve' => $this->intOrNull($instance['fans_gain'] ?? null, 1, 255),
            'is_mandatory' => in_array($slotId, self::MANDATORY_SLOTS, true),
            // The export states the maiden rule once, globally, on the maiden row —
            // "You can't participate in any races listed here until you win either
            // Debut or any of the Maiden Races" — and never per race. Asserting it
            // row by row here would be this parser's reading of a rule the source
            // does not encode per row, so it stays false and the question is filed
            // as a gap rather than answered in a parser.
            'is_maiden_gated' => false,
            'is_special_race' => (bool) ($instance['special_race'] ?? false),
            'did_not_exist' => is_string($instance['did_not_exist'] ?? null)
                ? (string) $instance['did_not_exist']
                : (is_string($details['did_not_exist'] ?? null) ? (string) $details['did_not_exist'] : null),
            'external_ref' => is_int($details['id'] ?? null) ? (string) $details['id'] : $slotId,
            'sort_order' => $half,
        ];
    }

    /**
     * A slot is `[Global]` career content unless it belongs to a scenario that never
     * shipped there. The finals are the only per-scenario rows, so only they can fail.
     */
    private function isGlobalCareerSlot(string $slotId, int $year): bool
    {
        if ($year === 4 && str_starts_with($slotId, 'final')) {
            return isset(self::GLOBAL_FINALS_BY_SLOT[$slotId]);
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function unreleasedOnGlobal(array $details): bool
    {
        $servers = $details['unreleased_servers'] ?? null;

        return is_array($servers) && in_array('en', $servers, true);
    }

    private function label(?int $month, int $half): string
    {
        if ($month === null) {
            return 'after Senior December';
        }

        return ($half === 2 ? 'Late ' : 'Early ').self::MONTHS[$month - 1];
    }

    /**
     * Distance bands are the published cut-offs carried in `09` §"Tier labels":
     * Sprint to 1400 m, Mile to 1800 m, Medium to 2400 m, Long above.
     */
    private function band(mixed $distance): ?string
    {
        if (! is_int($distance) || $distance === self::UNRESOLVED || $distance <= 0) {
            return null;
        }

        return match (true) {
            $distance <= 1400 => 'Sprint',
            $distance <= 1800 => 'Mile',
            $distance <= 2400 => 'Medium',
            default => 'Long',
        };
    }

    private function intOrNull(mixed $value, int $min, int $max): ?int
    {
        if (! is_int($value) || $value === self::UNRESOLVED || $value < $min || $value > $max) {
            return null;
        }

        return $value;
    }
}
