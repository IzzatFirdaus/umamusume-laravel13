<?php

declare(strict_types=1);

namespace App\Models\Legacy;

use InvalidArgumentException;

/**
 * What Legacy Select showed, recorded as the Trainer read it (D-260, D-268, ADR-0010).
 *
 * D-268 enumerates the state the screen holds and `training_runs` did not: per Legacy the chosen
 * Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with per-Spark
 * kind, target and star rank — plus an affinity grade for the pair. One payload keyed to the run
 * holds all of it, which is the shape D-268 proposes, rather than a wide table of nullable columns.
 *
 * It records and computes nothing. Whether a Spark would fire, what an affinity grade is worth in
 * stat points, and how a pair would combine are out of the tool by PRD §6 non-goal 3 and §6.11, and
 * ADR-0010 says where this class sits relative to that. Every figure here is something a Trainer read
 * off the client, in the same category as `RaceEntry::$circles`.
 *
 * The vocabulary is the `[Global]` one: Sparks, not the `[JP]` 因子, and Blue / Pink / Green / White /
 * Scenario as `docs/UMAMUSUME_REFERENCE.md` §1.5.2 renders them. §1.5.3 sets the three-star ceiling
 * on a Spark and §1.5.4 the △ / ○ / ◎ affinity grades.
 *
 * Three deliberate gaps:
 * - A Legacy's own rank is unbounded, because the corpus states that three stars guarantees a
 *   unique-skill Spark (§1.5.1) and states no ceiling on the character's own count; bounding it here
 *   would be inventing a number the sources do not carry.
 * - `SPARK_KINDS` is the `[Global]` **display** vocabulary. The export underneath it uses a different
 *   set, and the two do not line up: `REFERENCE` §1.5.2's Sources line names the dataset categories
 *   as `blue, pink, skill, race, scenario, other`, while its table renders `[Global]` as Blue, Pink,
 *   Green, White and Scenario Sparks — where one White row is the skill family and another is the
 *   competition family. So `white` here is one label over two dataset categories, and `green` is a
 *   dataset `skill`/unique-skill record under its screen name. A payload records what the Trainer read
 *   on screen, which is why the display set won; anything that later joins these rows to the dataset
 *   needs the mapping stated, not inferred, and no such join exists yet.
 * - `affinity` is one grade for the pair, which is what D-268 asks for. §1.5.4 grades each link in
 *   the diagram separately, and the four deeper links are not modelled here.
 */
final readonly class LegacySelectionPayload
{
    public const KEYS = ['legacies', 'affinity'];

    public const LEGACY_KEYS = ['rank', 'is_guest', 'ancestors', 'sparks'];

    public const SPARK_KEYS = ['kind', 'target', 'stars'];

    public const SPARK_KINDS = ['blue', 'pink', 'green', 'white', 'scenario'];

    public const AFFINITY_GRADES = ['△', '○', '◎'];

    public const MAX_SPARK_STARS = 3;

    public const MAX_ANCESTORS = 2;

    /**
     * The [Global] display labels for each Spark kind.
     *
     * @return array<string, string>
     */
    public static function sparkKindLabels(): array
    {
        return [
            'blue' => 'Blue',
            'pink' => 'Pink',
            'green' => 'Green',
            'white' => 'White',
            'scenario' => 'Scenario',
        ];
    }

    /**
     * @param  list<array{rank: int|null, is_guest: bool, ancestors: list<string|null>, sparks: list<array{kind: string, target: mixed, stars: int|null}>}>  $legacies
     */
    public function __construct(
        public array $legacies,
        public ?string $affinity,
    ) {}

    /**
     * @param  array<array-key, mixed>  $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertKeys($raw, self::KEYS, 'A Legacy Select payload');

        if (! is_array($raw['legacies'])) {
            throw new InvalidArgumentException('A Legacy Select payload needs a legacies list.');
        }

        $legacies = [];

        foreach ($raw['legacies'] as $index => $legacy) {
            $legacies[] = self::legacy($index, $legacy);
        }

        return new self($legacies, self::affinity($raw['affinity']));
    }

    /**
     * @return array{legacies: list<array<string, mixed>>, affinity: string|null}
     */
    public function toArray(): array
    {
        return [
            'legacies' => $this->legacies,
            'affinity' => $this->affinity,
        ];
    }

    /**
     * @return array{rank: int|null, is_guest: bool, ancestors: list<string|null>, sparks: list<array<string, mixed>>}
     */
    private static function legacy(int|string $index, mixed $legacy): array
    {
        if (! is_array($legacy)) {
            throw new InvalidArgumentException("Legacy #{$index} is not a record.");
        }

        self::assertKeys($legacy, self::LEGACY_KEYS, "Legacy #{$index}");

        if (! is_bool($legacy['is_guest'])) {
            throw new InvalidArgumentException("Legacy #{$index} needs a bool is_guest.");
        }

        if (! is_array($legacy['ancestors']) || ! is_array($legacy['sparks'])) {
            throw new InvalidArgumentException("Legacy #{$index} needs an ancestor list and a spark list.");
        }

        if (count($legacy['ancestors']) > self::MAX_ANCESTORS) {
            throw new InvalidArgumentException(
                'Legacy #'.$index.' names '.count($legacy['ancestors']).' ancestors; the diagram holds '
                .self::MAX_ANCESTORS.' per parent (REFERENCE §1.5.4).',
            );
        }

        // An ancestor is a name, not a slot record (`ADR-0010` Consequences §2). The position is the
        // slot, so a record with `slot`/`name` keys is a shape no writer produces and a reader must
        // not silently accept: it is what made the Inheritance page throw on a UI-created run.
        foreach ($legacy['ancestors'] as $ancestorIndex => $ancestor) {
            if ($ancestor !== null && ! is_string($ancestor)) {
                throw new InvalidArgumentException(
                    "Legacy #{$index} ancestor #{$ancestorIndex} is not a name: ".gettype($ancestor).'.',
                );
            }
        }

        $sparks = [];

        foreach ($legacy['sparks'] as $sparkIndex => $spark) {
            $sparks[] = self::spark($index, $sparkIndex, $spark);
        }

        return [
            'rank' => self::nullableInt($legacy['rank'], "Legacy #{$index} rank"),
            'is_guest' => $legacy['is_guest'],
            'ancestors' => array_values($legacy['ancestors']),
            'sparks' => $sparks,
        ];
    }

    /**
     * @return array{kind: string, target: mixed, stars: int|null}
     */
    private static function spark(int|string $legacyIndex, int|string $sparkIndex, mixed $spark): array
    {
        $where = "Spark #{$sparkIndex} on Legacy #{$legacyIndex}";

        if (! is_array($spark)) {
            throw new InvalidArgumentException("{$where} is not a record.");
        }

        self::assertKeys($spark, self::SPARK_KEYS, $where);

        if (! is_string($spark['kind']) || ! in_array($spark['kind'], self::SPARK_KINDS, true)) {
            $given = is_string($spark['kind']) ? $spark['kind'] : gettype($spark['kind']);

            throw new InvalidArgumentException(
                'A spark kind is one of '.implode(', ', self::SPARK_KINDS)."; [{$given}] is not.",
            );
        }

        $stars = self::nullableInt($spark['stars'], "{$where} stars");

        if ($stars !== null && ($stars < 1 || $stars > self::MAX_SPARK_STARS)) {
            throw new InvalidArgumentException(
                "{$where} stars [{$stars}] is outside 1 to ".self::MAX_SPARK_STARS
                .': three is the ceiling, and it rolls rather than being chosen (REFERENCE §1.5.3).',
            );
        }

        return ['kind' => $spark['kind'], 'target' => $spark['target'], 'stars' => $stars];
    }

    private static function affinity(mixed $affinity): ?string
    {
        if ($affinity === null) {
            return null;
        }

        if (! is_string($affinity) || ! in_array($affinity, self::AFFINITY_GRADES, true)) {
            throw new InvalidArgumentException(
                'An affinity grade is one of '.implode(' ', self::AFFINITY_GRADES)
                .' or nothing at all (REFERENCE §1.5.4).',
            );
        }

        return $affinity;
    }

    private static function nullableInt(mixed $value, string $label): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value)) {
            throw new InvalidArgumentException("{$label} needs an int or null.");
        }

        return $value;
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
