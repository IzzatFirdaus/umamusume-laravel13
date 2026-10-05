<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The six deck-analysis categories, computed only from the anchors the source states.
 *
 * Every figure here is an anchor `SupportCardEffects::atCap()` already resolved at a card's highest
 * stated level, or an integer added up from those anchors. Nothing is interpolated between levels, and
 * nothing is ranked or normalised: a deck has no score, and the sentences that read as "strengths" and
 * "weaknesses" are restatements of the counts beside them rather than a verdict.
 *
 * The category membership is the shelving in `UMAMUSUME_REFERENCE.md` §1.4.8, which re-shelves the
 * export's own `calc` and `symbol` fields rather than inventing a grouping. §1.4.7 and
 * `SUPPORT-CARDS.md` hold the anchor values themselves, so a category that names an effect the
 * dictionary has no row for prints the source's own id instead of a word (`D-20`).
 *
 * The one judgement call is arithmetic, and it reads the export's own `calc`. Only the effects the
 * export marks `add`, or states nothing about at all, may be added across cards; the three marked
 * `mult` list each card's value and stop, because a total of those is a figure the client does not
 * state.
 *
 * @phpstan-type EffectLine = array{
 *     effect_id: int,
 *     name: string|null,
 *     mode: string,
 *     cards: list<array{card_name: string, display: string}>,
 *     total: array{value: int, display: string, basis: string}|null
 * }
 * @phpstan-type EffectEntry = array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}
 * @phpstan-type CardInput = array{card_name: string, effects: list<EffectEntry>}
 */
class DeckAnalysis
{
    /**
     * Category key => the effect ids that land there, in the order §1.4.8 lists them.
     *
     * `skills` carries the hint-output effects only. The card's own hint and event lists are the
     * per-card facts `SCR-SUP-002` already renders, and counting them here would re-derive a
     * resolvable-versus-unlinked split this screen has no link surface for.
     *
     * @var array<string, list<int>>
     */
    public const CATEGORIES = [
        'training_power' => [1, 2, 8, 19, 3, 4, 5, 6, 7, 30],
        'early_run' => [9, 10, 11, 12, 13, 14],
        'race_bonus' => [15, 16],
        'safety' => [27, 28, 31],
        'events' => [25, 26],
        'skills' => [17, 18, 32],
    ];

    /**
     * The category labels a Trainer reads, keyed by the same tokens as `CATEGORIES`.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        'training_power' => 'Training power',
        'early_run' => 'Early run',
        'race_bonus' => 'Race bonus',
        'safety' => 'Safety',
        'events' => 'Events',
        'skills' => 'Skills',
    ];

    /**
     * The nine effects §1.4.8 records as carried by no card at all.
     *
     * They sit in no category, because a category that can only ever be blank is a heading with nothing
     * under it. They are named instead so that a deck holding one does not go silent, which is what
     * keeps the two lists a partition of the thirty-five published effects rather than of twenty-six.
     *
     * @var list<int>
     */
    public const UNCATEGORISED = [20, 21, 22, 23, 24, 29, 33, 41, 9991];

    /**
     * The deck summary for the six cards equipped, by category.
     *
     * An effect carried by no card is not listed: a category that would otherwise be a wall of zeros is
     * a category with no data, and its absence is what the `strengths` and `weaknesses` sentences
     * state.
     *
     * @param  list<self::CardInput>  $cards
     * @return array{
     *   covered: int,
     *   categories: array<string, array{label: string, blank: bool, effects: list<self::EffectLine>}>,
     *   uncategorised: list<self::EffectLine>,
     *   strengths: list<string>,
     *   weaknesses: list<string>
     * }
     */
    public static function build(array $cards): array
    {
        $covered = count($cards);
        $categories = [];

        foreach (self::CATEGORIES as $key => $effectIds) {
            $byEffect = [];

            foreach ($cards as $card) {
                foreach ($card['effects'] as $effect) {
                    if (in_array($effect['effect_id'], $effectIds, true)) {
                        self::collect($effect, $card['card_name'], $byEffect);
                    }
                }
            }

            $lines = self::lines($byEffect);

            $categories[$key] = ['label' => self::LABELS[$key], 'blank' => $lines === [], 'effects' => $lines];
        }

        return [
            'covered' => $covered,
            'categories' => $categories,
            'uncategorised' => self::lines(self::uncategorised($cards)),
            'strengths' => self::strengths($categories, $covered),
            'weaknesses' => self::weaknesses($categories),
        ];
    }

    /**
     * Every effect in the deck that is in no category, gathered the same way the categories gather
     * theirs so it gets the same mode and total treatment.
     *
     * @param  list<self::CardInput>  $cards
     * @return array<int, array<string, mixed>>
     */
    private static function uncategorised(array $cards): array
    {
        $categorised = [];

        foreach (self::CATEGORIES as $ids) {
            foreach ($ids as $id) {
                $categorised[$id] = true;
            }
        }

        $byEffect = [];

        foreach ($cards as $card) {
            foreach ($card['effects'] as $effect) {
                if (! isset($categorised[$effect['effect_id']])) {
                    self::collect($effect, $card['card_name'], $byEffect);
                }
            }
        }

        return $byEffect;
    }

    /**
     * @param  self::EffectEntry  $effect
     * @param  array<int, array<string, mixed>>  $byEffect
     */
    private static function collect(array $effect, string $cardName, array &$byEffect): void
    {
        $entry = &$byEffect[$effect['effect_id']];

        if ($entry === null) {
            $entry = [
                'name' => $effect['name'],
                'calc' => $effect['calc'],
                'symbol' => $effect['symbol'],
                'cards' => [],
                'values' => [],
            ];
        }

        $entry['cards'][] = ['card_name' => $cardName, 'display' => $effect['display']];
        $entry['values'][] = $effect['value'];
    }

    /**
     * The gathered entries as the lines the surface prints, one per effect, in the order the first
     * card carrying it met it.
     *
     * @param  array<int, array<string, mixed>>  $byEffect
     * @return list<self::EffectLine>
     */
    private static function lines(array $byEffect): array
    {
        $lines = [];

        foreach ($byEffect as $id => $entry) {
            $mode = self::mode($entry['calc']);
            $sum = array_sum($entry['values']);

            $lines[] = [
                'effect_id' => $id,
                'name' => $entry['name'],
                'mode' => $mode,
                'cards' => $entry['cards'],
                'total' => $mode === 'multiplicative' ? null : [
                    'value' => $sum,
                    'display' => SupportCardEffects::formatValue($sum, $entry['symbol'] ?? null),
                    'basis' => $mode === 'additive' ? 'added' : 'summed',
                ],
            ];
        }

        return $lines;
    }

    /**
     * The combining mode the export states, in the words §1.4.8 uses rather than the column's own
     * values: `calc` holds only `mult` and `add`, and the effects that declare nothing are the ones
     * the reference calls flat.
     */
    private static function mode(?string $calc): string
    {
        return match ($calc) {
            'mult' => 'multiplicative',
            'add' => 'additive',
            default => 'flat',
        };
    }

    /**
     * One line per category the deck carries something in: the category, and how many of the cards
     * equipped carry an effect in it. A count restated in words, with no threshold deciding whether a
     * number is good.
     *
     * @param  array<string, array{label: string, blank: bool, effects: list<self::EffectLine>}>  $categories
     * @return list<string>
     */
    private static function strengths(array $categories, int $covered): array
    {
        $lines = [];

        foreach ($categories as $key => $category) {
            if ($category['blank']) {
                continue;
            }

            $carriers = [];

            foreach ($category['effects'] as $effect) {
                foreach ($effect['cards'] as $card) {
                    $carriers[$card['card_name']] = true;
                }
            }

            $lines[] = sprintf('%s: %d of the %d cards carry an effect here.', $category['label'], count($carriers), $covered);
        }

        return $lines;
    }

    /**
     * One line per category the deck carries nothing in. The category names the effect group, so the
     * Trainer can tell "no card has this" from "I have not looked yet".
     *
     * @param  array<string, array{label: string, blank: bool, effects: list<self::EffectLine>}>  $categories
     * @return list<string>
     */
    private static function weaknesses(array $categories): array
    {
        $lines = [];

        foreach ($categories as $key => $category) {
            if (! $category['blank']) {
                continue;
            }

            $lines[] = sprintf('%s: no card in the deck carries an effect here.', $category['label']);
        }

        return $lines;
    }
}
