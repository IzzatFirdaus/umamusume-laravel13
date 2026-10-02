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
     * `x-rarity-chip` puts in its aria-label) while the word the Global client prints is R / SR / SSR
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
}
