<?php

declare(strict_types=1);

namespace App\Enums;

enum CardRarity: int
{
    use HasLabel;

    case OneStar = 1;
    case TwoStar = 2;
    case ThreeStar = 3;

    /**
     * The completed breaks a `[Global]` card can take. Four is the ceiling, so 0 is an unbroken copy
     * and 4 is Max Limit Break (`UMAMUSUME_REFERENCE.md` §1.4.2). On the enum beside `word()` because
     * the deck step has to name the top of the ladder without copying the number into a renderer.
     */
    public const MAX_BREAKS = 4;

    /**
     * The glyph run is the ordinal signal. Hue cannot order three near-identical
     * fills and G-6 will not accept a colour-only readout, for the same reason
     * mood-pill.blade.php pairs each fill with an arrow.
     */
    public function stars(): string
    {
        return str_repeat('★', $this->value);
    }

    /**
     * The client's rarity word, which is not `label()`: `label()` reads "Three Star" (the order signal
     * `RarityChip.vue` puts in its aria-label) while the word the Global client prints is R / SR / SSR
     * (UMAMUSUME_REFERENCE.md §1.4.2). On the enum rather than on `SupportCard` because a filter facet
     * has to name a rarity that is not a row yet, and two copies of the mapping would drift.
     */
    public function word(): string
    {
        return match ($this) {
            self::OneStar => 'R',
            self::TwoStar => 'SR',
            self::ThreeStar => 'SSR',
        };
    }

    /**
     * The level a card of this rarity starts at, before any break.
     *
     * The client's own readout: the deck editor draws four diamonds per slot and fills one per
     * completed break, and the level beside them is `base + 5 × breaks` — so the unbroken bases are
     * SSR 30, SR 25, R 20 and the four-break caps are 50 / 45 / 40 (`UMAMUSUME_REFERENCE.md` §1.4.2).
     * The export's per-rarity effect ladders stop at exactly those caps, which is the second source for
     * the same set. Held here rather than in a renderer because the cap is a property of the rarity,
     * is read by two screens, and is never a stored column (`ADR-0014` declines the collection).
     */
    public function baseLevel(): int
    {
        return match ($this) {
            self::ThreeStar => 30,
            self::TwoStar => 25,
            self::OneStar => 20,
        };
    }

    /**
     * The level a card of this rarity can reach after the given number of completed breaks.
     *
     * Five levels per break (§1.4.2), so `levelAtBreak(self::MAX_BREAKS)` is the fully broken cap and
     * `levelAtBreak(0)` is `baseLevel()`.
     */
    public function levelAtBreak(int $breaks): int
    {
        return $this->baseLevel() + (5 * $breaks);
    }
}
