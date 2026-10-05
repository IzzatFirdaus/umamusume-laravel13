<?php

declare(strict_types=1);

namespace App\Models\Advisor;

use App\Enums\BuildPurpose;
use InvalidArgumentException;

/**
 * What the Trainer intends to build, recorded as they entered it (FR-F-1, `ADR-0020` §2, `SCREEN-005`).
 *
 * One payload keyed to the run, held in the `build_target` json column, rather than a table of
 * nullable columns: the brief calls this object "Your target" and it is entered and replaced as a
 * unit, so a row per field would carry six nulls for a run that has no target at all and would
 * make "not set" and "set to nothing" the same shape. `LegacySelectionPayload` made the same call
 * for the same reason (`ADR-0010`).
 *
 * It records and computes nothing. The advisor reads it to rank options against entered targets
 * (`ADR-0020` §2); nothing here decides whether a target is *good*, reachable in the turns left, or
 * worth pursuing. Whether a stat can reach a target is `ScenarioCaps`' question and is enforced by
 * the Form Request at the boundary, not here — a stored payload is a record of intent, and a run
 * whose scenario changed underneath it must still read back what was entered rather than throw.
 *
 * The vocabularies are sourced, not invented:
 * - `DISTANCE_BANDS` and `SURFACES` are the values `race_catalog_slots` actually publishes.
 * - `STYLES` is the `[Global]` client vocabulary from `lang/en/uma.php`, because a payload records
 *   what the Trainer read on screen. The keys in that map (`style_front_runner`) are language keys,
 *   not stored values, and the two must not be confused.
 * - `purpose` is a `BuildPurpose` case rather than a label: its four cases are this tool's contract
 *   values and the brief's "General Training" is one of them under a different name, so accepting
 *   the label here would widen the vocabulary to whatever a form happened to print.
 *
 * `targets` is keyed by the stat matrix's own names and must carry exactly them. A four-stat target
 * or a six-stat one would leave the advisor ranking against a stat the run does not hold, and a
 * partial match is worse than a refusal because it looks like a complete target.
 */
final readonly class BuildTargetPayload
{
    public const KEYS = ['purpose', 'distance', 'surface', 'style', 'targets', 'skill_priorities'];

    public const DISTANCE_BANDS = ['Sprint', 'Mile', 'Medium', 'Long'];

    public const SURFACES = ['Turf', 'Dirt'];

    public const STYLES = ['Front Runner', 'Pace Chaser', 'Late Surger', 'End Closer'];

    /**
     * @param  array<string, int>  $targets  keyed by the stat matrix's names, exactly
     * @param  list<string>  $skillPriorities
     */
    public function __construct(
        public BuildPurpose $purpose,
        public string $distance,
        public string $surface,
        public string $style,
        public array $targets,
        public array $skillPriorities,
    ) {}

    /**
     * @param  array<array-key, mixed>  $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertKeys($raw, self::KEYS, 'A build target');

        return new self(
            self::purpose($raw['purpose']),
            self::oneOf($raw['distance'], self::DISTANCE_BANDS, 'distance band'),
            self::oneOf($raw['surface'], self::SURFACES, 'surface'),
            self::oneOf($raw['style'], self::STYLES, 'running style'),
            self::targets($raw['targets']),
            self::skillPriorities($raw['skill_priorities']),
        );
    }

    /**
     * @return array{purpose: string, distance: string, surface: string, style: string, targets: array<string, int>, skill_priorities: list<string>}
     */
    public function toArray(): array
    {
        return [
            'purpose' => $this->purpose->value,
            'distance' => $this->distance,
            'surface' => $this->surface,
            'style' => $this->style,
            'targets' => $this->targets,
            'skill_priorities' => $this->skillPriorities,
        ];
    }

    private static function purpose(mixed $value): BuildPurpose
    {
        $case = is_string($value) ? BuildPurpose::tryFrom($value) : null;

        if ($case === null) {
            $given = is_string($value) ? $value : gettype($value);

            throw new InvalidArgumentException(
                'A build purpose is one of '
                .implode(', ', array_map(static fn (BuildPurpose $case): string => $case->value, BuildPurpose::cases()))
                ."; [{$given}] is not. The cases are this tool's contract values, not client strings.",
            );
        }

        return $case;
    }

    /**
     * @param  list<string>  $allowed
     */
    private static function oneOf(mixed $value, array $allowed, string $label): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            $given = is_string($value) ? $value : gettype($value);

            throw new InvalidArgumentException(
                "A {$label} is one of ".implode(', ', $allowed)."; [{$given}] is not.",
            );
        }

        return $value;
    }

    /**
     * @return array<string, int>
     */
    private static function targets(mixed $targets): array
    {
        if (! is_array($targets)) {
            throw new InvalidArgumentException('A build target needs a targets map.');
        }

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        // Exactly the matrix's stats, in its order, so the read-back cannot carry a stat the run
        // does not have and cannot quietly drop one it does.
        self::assertKeys($targets, $order, 'A build target targets');

        $clean = [];

        foreach ($order as $stat) {
            $value = $targets[$stat];

            if (! is_int($value)) {
                $given = gettype($value);

                throw new InvalidArgumentException("The {$stat} target needs an int; it carries a {$given}.");
            }

            $clean[$stat] = $value;
        }

        return $clean;
    }

    /**
     * @return list<string>
     */
    private static function skillPriorities(mixed $priorities): array
    {
        if (! is_array($priorities) || ! array_is_list($priorities)) {
            throw new InvalidArgumentException('A build target needs skill_priorities as a list of names.');
        }

        $clean = [];

        foreach ($priorities as $index => $name) {
            if (! is_string($name) || $name === '') {
                $given = is_string($name) ? 'an empty string' : gettype($name);

                throw new InvalidArgumentException(
                    "skill_priorities #{$index} is not a skill name; it carries {$given}.",
                );
            }

            $clean[] = $name;
        }

        return $clean;
    }

    /**
     * A json column accepts any key set, so a typo becomes a row that reads as nothing. Name the
     * offending keys rather than printing the expected shape and leaving the caller to diff them.
     *
     * @param  array<array-key, mixed>  $row
     * @param  list<string>  $expected
     */
    private static function assertKeys(array $row, array $expected, string $what): void
    {
        $unexpected = array_diff(array_keys($row), $expected);
        $missing = array_diff($expected, array_keys($row));

        if ($unexpected === [] && $missing === []) {
            return;
        }

        throw new InvalidArgumentException(
            "{$what} carries exactly [".implode(', ', $expected).'] keys'
            .($unexpected === [] ? '' : ', with unknown key '.implode(', ', $unexpected))
            .($missing === [] ? '' : ', missing '.implode(', ', $missing)).'.'
        );
    }
}
