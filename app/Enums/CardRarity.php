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
}
