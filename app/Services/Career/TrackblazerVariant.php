<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\TrainingRun;
use App\Models\Umamusume;

/**
 * Which Trackblazer Result Point track a trainee's aptitudes select (ruling F1).
 *
 * **The rule is the publisher's, not this class's.** Cygames' Trackblazer launch announcement states
 * "The required number of Result Points is determined based on your trainee's year and aptitudes"
 * (`docs/UMAMUSUME_REFERENCE.md` §2.2.3, reproduced by DotGG from the announcement; the same sentence
 * is quoted at `docs/research-scratch/PLANS-AND-BRIEFS.md` §"ruling-j-twinkle-star-climax-2026-11-12.md"
 * fact row 3). Selecting a track therefore is not a prediction: it chooses which *published*
 * requirement applies. `config/scenarios.php` carries all three tracks; before this class nothing
 * chose between them, which is KI-15.
 *
 * **Four classifications, and the fourth is the point.** `dirt_leaning` and `limited_turf_range` are
 * the two named variant tracks. `ambiguity` is where two surfaces are both strong and no source breaks
 * the tie: 8 trainees carry turf ≥ B *and* dirt ≥ B, and three of them (Agnes Digital, Inari One,
 * Victoire Pisa) are exact A/A ties that no field ordering can separate. Those 8 are **classified**
 * `ambiguous` and **render** the `standard` track, with the reason printed — ruling F1's explicit
 * fallback, and the reason the naive threshold rule (which silently assigns all 8 to `dirt_leaning`)
 * is not what is implemented here.
 *
 * **The ordering is stated, not emergent.** Three predicates, applied in the order below, and each
 * returns on its own. On today's roster the three sets are disjoint, so all six orderings happen to
 * produce the measured 9 / 3 / 8 / 113 — which is exactly why the order is written down rather than
 * left to whichever `if` came first. A future roster where they overlap resolves by this order and by
 * nothing else (no array ordering, no sorting, no fallback).
 *
 * **Missing aptitude is not `standard`.** A trainee whose source published no aptitude letters has no
 * classification at all: `classification` is null and `reason` names the absence. The rendered track
 * still falls back to `standard`, because a wrong denominator is worse than the conservative one
 * (D-256), but the payload never claims the trainee was classified. `Umamusume::aptitudeAxes()` takes
 * the same position on the same data: an absent aptitude set is not ten unknown letters.
 */
final class TrackblazerVariant
{
    /**
     * The standard track, which is both a track key in the config and one of the four classifications.
     *
     * The two readings differ only for `ambiguous` and for an unclassified trainee, which both RENDER
     * this track without being classified by it — which is why the payload carries `classification` and
     * `track` as separate keys rather than one word doing both jobs.
     */
    public const STANDARD = 'standard';

    /** The two variant tracks, named exactly as the config keys are. */
    public const DIRT_LEANING = 'dirt_leaning';

    public const LIMITED_TURF_RANGE = 'limited_turf_range';

    /** The fourth classification: a real reading that names no track. */
    public const AMBIGUOUS = 'ambiguous';

    /**
     * The four classifications this rule can produce, in the order they are decided.
     *
     * `standard` is both a classification and a track key, which is why the two travel separately in
     * the payload: `ambiguous` renders the `standard` track without being classified `standard`.
     *
     * @var list<string>
     */
    public const CLASSIFICATIONS = [self::DIRT_LEANING, self::AMBIGUOUS, self::LIMITED_TURF_RANGE, self::STANDARD];

    /**
     * The aptitude letters, weakest to strongest.
     *
     * The six aptitude columns carry these and only these; `S` and `S+` are the team-rank vocabulary
     * (`TeamRankPayload::LETTERS`), a different ladder, and are not accepted here.
     *
     * @var list<string>
     */
    public const LETTERS = ['G', 'F', 'E', 'D', 'C', 'B', 'A'];

    /**
     * The two-surface threshold ruling F1 words as "turf ≥ B *and* dirt ≥ B".
     *
     * B is the letter both variant tracks' sources treat as the boundary: Haru Urara's dirt A against
     * turf G is the dirt case, Curren Chan's turf A against dirt F is the limited-range case, and a
     * trainee at B on both surfaces is the case neither source separates.
     */
    public const STRONG = 'B';

    /**
     * The variant for a run's own trainee.
     *
     * Delegates rather than reading rows, so the Cockpit, the panel builder and Preflight all get one
     * answer from one place. A run whose scenario is not Trackblazer gets `null` here **and** is
     * refused by `gradeObjectives()`, so no other scenario can receive a Trackblazer variant.
     *
     * @return array{
     *     classification: string|null,
     *     track: string,
     *     reason: string,
     *     evidence: list<array{label: string, letter: string}>
     * }|null
     */
    public static function forRun(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return self::forTrainee($run->umamusume);
    }

    /**
     * The variant for one trainee, or null when there is no trainee to classify.
     *
     * @return array{
     *     classification: string|null,
     *     track: string,
     *     reason: string,
     *     evidence: list<array{label: string, letter: string}>
     * }|null
     */
    public static function forTrainee(?Umamusume $trainee): ?array
    {
        if ($trainee === null) {
            return null;
        }

        return self::derive(
            $trainee->aptitude_turf,
            $trainee->aptitude_dirt,
            $trainee->aptitude_sprint,
            $trainee->aptitude_mile,
            $trainee->aptitude_medium,
            $trainee->aptitude_long,
        );
    }

    /**
     * The ordered derivation, from the six aptitude letters.
     *
     * The three predicates, applied in this order:
     *
     *   1. `dirt_leaning` — dirt outranks turf. The highest aptitude wins, and it is on the dirt side.
     *   2. `ambiguous` — turf and dirt are *both* at B or better. Two strong surfaces with no published
     *      tie-break, so the trainee is classified and the standard track is rendered with the reason.
     *   3. `limited_turf_range` — turf outranks dirt, sprint is at B or better, and every other distance
     *      is below B. Turf is the surface and short is the only range the trainee holds.
     *
     * Anything else is `standard`. Note that 2 is checked before 3: a trainee at turf A / dirt B is
     * ambiguous even where sprint is strong, because the surface question is unsettled and the range
     * question cannot be asked until it is.
     *
     * @return array{
     *     classification: string|null,
     *     track: string,
     *     reason: string,
     *     evidence: list<array{label: string, letter: string}>
     * }
     */
    public static function derive(
        ?string $turf,
        ?string $dirt,
        ?string $sprint,
        ?string $mile,
        ?string $medium,
        ?string $long,
    ): array {
        $letters = [
            ['label' => 'Turf', 'letter' => $turf],
            ['label' => 'Dirt', 'letter' => $dirt],
            ['label' => 'Sprint', 'letter' => $sprint],
            ['label' => 'Mile', 'letter' => $mile],
            ['label' => 'Medium', 'letter' => $medium],
            ['label' => 'Long', 'letter' => $long],
        ];

        // A partially recorded set is as unclassifiable as an empty one. Both are stated as an absence
        // rather than completed from the letters that happen to be present: the missing axis is what a
        // predicate would have read. An unrecognised letter is a third case and says so, because "not
        // recorded" would be a false statement about a column that holds a value.
        $missing = array_map(
            static fn (array $row): string => $row['label'],
            array_values(array_filter(
                $letters,
                static fn (array $row): bool => $row['letter'] === null || self::rank($row['letter']) === null,
            )),
        );

        if ($missing !== []) {
            $unrecorded = array_values(array_filter(
                $letters,
                static fn (array $row): bool => $row['letter'] === null,
            ));

            return [
                'classification' => null,
                'track' => self::STANDARD,
                'reason' => ($unrecorded === []
                    ? 'This trainee carries an aptitude letter the ladder does not recognise ('
                        .implode(', ', $missing).').'
                    : 'This trainee\'s aptitude letters are not recorded on '
                        .implode(' or ', $missing).'.')
                    .' The classification needs turf, dirt and all four distances, so none is derived and the '
                    .'standard track is shown rather than one guessed from the letters that are present.',
                'evidence' => array_values(array_filter(
                    $letters,
                    static fn (array $row): bool => $row['letter'] !== null,
                )),
            ];
        }

        $turfRank = (int) self::rank($turf);
        $dirtRank = (int) self::rank($dirt);
        $strong = (int) self::rank(self::STRONG);

        if ($dirtRank > $turfRank) {
            return [
                'classification' => self::DIRT_LEANING,
                'track' => self::DIRT_LEANING,
                'reason' => 'Dirt '.$dirt.' outranks turf '.$turf.', so the dirt-leaning track applies.',
                'evidence' => $letters,
            ];
        }

        if ($turfRank >= $strong && $dirtRank >= $strong) {
            $tie = $turf === $dirt;

            return [
                'classification' => self::AMBIGUOUS,
                'track' => self::STANDARD,
                'reason' => 'Turf '.$turf.' and dirt '.$dirt.' are both at '.self::STRONG.' or better, and no '
                    .'published source breaks the tie'
                    .($tie
                        ? ': the two aptitudes are exactly equal, and no field ordering separates them.'
                        : ': the higher letter is the surface the requirement follows, but no source states it.')
                    .' The standard track is shown because a wrong denominator is worse than the conservative '
                    .'one, and this classification is not silently resolved to either variant.',
                'evidence' => $letters,
            ];
        }

        $otherDistances = [$mile, $medium, $long];
        $sprintOnly = (int) self::rank($sprint) >= $strong
            && array_filter($otherDistances, static fn (?string $letter): bool => (int) self::rank($letter) >= $strong) === [];

        if ($turfRank > $dirtRank && $sprintOnly) {
            return [
                'classification' => self::LIMITED_TURF_RANGE,
                'track' => self::LIMITED_TURF_RANGE,
                'reason' => 'Turf '.$turf.' outranks dirt '.$dirt.' and sprint '.$sprint.' is the only distance at '
                    .self::STRONG.' or better, so the limited-turf-range track applies.',
                'evidence' => $letters,
            ];
        }

        return [
            'classification' => self::STANDARD,
            'track' => self::STANDARD,
            'reason' => 'Turf '.$turf.' and dirt '.$dirt.' match neither variant\'s condition, so the standard '
                .'track applies.',
            'evidence' => $letters,
        ];
    }

    /**
     * A letter's position on the aptitude ladder, or null when it is not one of the seven.
     *
     * Null rather than 0, so an unrecognised letter fails the same test a missing one does instead of
     * ranking below `G` and silently satisfying a comparison.
     */
    private static function rank(?string $letter): ?int
    {
        if ($letter === null) {
            return null;
        }

        $index = array_search($letter, self::LETTERS, true);

        return $index === false ? null : (int) $index;
    }
}
