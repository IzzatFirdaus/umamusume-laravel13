<?php

declare(strict_types=1);

namespace App\Services\Legacy;

use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Veteran;

/**
 * The six-node ancestry graph, shaped from what a payload and two names already hold.
 *
 * `REFERENCE` §1.5.4: two parents, each bringing two ancestors of her own, so the diagram holds six.
 * That is `LegacySelectionPayload::legacies[2].ancestors[2]` exactly. This class adds no fact: it turns
 * a stored payload into the node list a Legacy surface prints, and it is the one place that shape is
 * decided. `LegacyController` (the run-scoped builder and the compare surface) and
 * `Career\LegacySelectController` (the setup wizard's ancestry step) both read through it, so the run
 * screen and the draft screen cannot disagree about what a node is.
 *
 * **It computes nothing** (`ADR-0020` §3, PRD FR-G-4). No inherited stat, no Affinity payout, no Spark
 * roll, no score, no ranking, no recommended combination. The `probability` every node carries is a
 * permanent named absence: this repository holds no sourced star-roll table, so the value is null and
 * the reason travels with it in the `title` rather than in page copy that a later reader can lose.
 *
 * **Names come from the caller, not from here.** The payload holds no name for a parent (the owner's
 * `ADR-0010` ruling kept `training_runs.inheritance_parent_a_id` and `_b_id` as the parent's identity
 * and put only the read-back details in the json), so the two parent names and the trainee name arrive
 * as arguments: from the run's own foreign keys on the run-scoped surfaces, from the draft's
 * `legacy_parents` on the wizard step. A node whose name nobody holds is null here and renders `N/A` on
 * the page, which is a real state and not a gap in the query.
 */
final class AncestryGraph
{
    /**
     * The one Spark kind a node can show per row, for the count beside each chip. A node with three
     * White Sparks prints `3 White`; the breakdown per Spark is on the node itself.
     *
     * The `[Global]` display words for `LegacySelectionPayload::SPARK_KINDS`, and the single owner of
     * that pairing: a surface that re-typed them would drift from the payload's vocabulary silently.
     *
     * @var array<string, string>
     */
    public const SPARK_KIND_LABELS = [
        'blue' => 'Blue',
        'pink' => 'Pink',
        'green' => 'Green',
        'white' => 'White',
        'scenario' => 'Scenario',
    ];

    /**
     * The graph as the surface prints it: the trainee, then Parent A with her two ancestors, then
     * Parent B with hers, in the client's own order.
     *
     * `rank`, `is_guest` and every star count stay nullable because a half-read screen is a real state
     * the payload exists to hold, so a null is `null` here rather than a zero or a default.
     *
     * @return array{trainee: array{name: string|null}, parents: list<array<string, mixed>>}
     */
    public static function build(
        ?LegacySelectionPayload $payload,
        ?string $traineeName,
        ?string $parentAName,
        ?string $parentBName,
    ): array {
        return [
            'trainee' => ['name' => $traineeName],
            'parents' => [
                self::parentNode($payload?->legacies[0] ?? null, 'parent_a', 'Parent A', 'Grandparent A1', 'Grandparent A2', $parentAName),
                self::parentNode($payload?->legacies[1] ?? null, 'parent_b', 'Parent B', 'Grandparent B1', 'Grandparent B2', $parentBName),
            ],
        ];
    }

    /**
     * The two parent names behind a pair of library picks, in slot order.
     *
     * A Veteran is a run whose trainee is the Umamusume the client shows in the parent slot, so the name is
     * read through the run rather than stored a second time (`ADR-0010` Decision keeps the two foreign keys
     * as the identity and the json as the read-back). The wizard step and Preflight both hold their
     * identity as `veterans` ids, so both resolve names here instead of each writing the join.
     *
     * @param  array{0: int|null, 1: int|null}  $veteranIds
     * @return array{0: string|null, 1: string|null}
     */
    public static function parentNames(array $veteranIds): array
    {
        /** @var array{0: string|null, 1: string|null} $names */
        $names = array_map(
            static fn (?int $id): ?string => $id === null
                ? null
                : Veteran::query()->with('trainingRun.umamusume')->find($id)?->trainingRun?->umamusume?->name,
            $veteranIds,
        );

        return $names;
    }

    /**
     * One parent node and her own two, with everything the payload does not hold named as absent.
     *
     * @param  array<string, mixed>|null  $legacy
     * @return array<string, mixed>
     */
    private static function parentNode(
        ?array $legacy,
        string $slot,
        string $parentLabel,
        string $a1,
        string $a2,
        ?string $name = null,
    ): array {
        $sparks = [];

        /** @var list<array{kind: string, target: mixed, stars: int|null}> $recorded */
        $recorded = $legacy['sparks'] ?? [];

        foreach ($recorded as $spark) {
            $sparks[] = [
                'kind' => (string) $spark['kind'],
                'kind_label' => self::SPARK_KIND_LABELS[(string) $spark['kind']] ?? ucfirst((string) $spark['kind']),
                'target' => $spark['target'] === null ? null : (string) $spark['target'],
                'stars' => $spark['stars'],
            ];
        }

        $counts = [];

        foreach (self::SPARK_KIND_LABELS as $kind => $label) {
            $counts[] = [
                'kind' => $kind,
                'kind_label' => $label,
                'count' => count(array_filter($sparks, static fn (array $spark): bool => $spark['kind'] === $kind)),
            ];
        }

        $ancestors = [];

        /** @var list<mixed> $ancestorNames */
        $ancestorNames = $legacy['ancestors'] ?? [];

        // `$ancestorName`, not `$name`: the loop variable must not shadow the parent node's own name
        // parameter, or the last ancestor read wins and Parent A renders as her own grandparent. That
        // bug is invisible in the ancestor rows (they read correctly) and corrupts only the parent name
        // one line later, which is exactly the kind of defect a props assertion on all six nodes
        // catches and a rendered-copy check does not.
        foreach ([[$a1, 0], [$a2, 1]] as [$label, $index]) {
            $ancestorName = $ancestorNames[$index] ?? null;

            $ancestors[] = [
                'slot' => $label,
                'name' => $ancestorName === null ? null : (string) $ancestorName,
            ];
        }

        return [
            'slot' => $slot,
            'label' => $parentLabel,
            'name' => $name,
            'rank' => isset($legacy['rank']) ? (int) $legacy['rank'] : null,
            'is_guest' => (bool) ($legacy['is_guest'] ?? false),
            'ancestors' => $ancestors,
            'sparks' => $sparks,
            'spark_counts' => $counts,
            'probability' => [
                'value' => null,
                // Named on the element itself rather than only in the page's copy, so the reason
                // travels with the value and cannot be lost by a reader that only sees the table.
                'title' => 'This tool holds no sourced star-roll table, so it states no chance for a Spark. '
                    .'The published odds are a wiki pair flagged stale in REFERENCE §1.5.3.',
            ],
        ];
    }
}
