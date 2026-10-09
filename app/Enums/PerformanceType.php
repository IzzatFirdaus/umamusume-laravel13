<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The five types of Performance, the resource Our Grand Concert pays alongside stats
 * (`docs/scenarios/07-grand-concert.md`, §"The loop, in order").
 *
 * **These are Global client strings, and that is the opposite of `SpiritBurstState`.**
 * Cygames' `[Global]` notice 905, read from the official news API on 2026-10-05, prints
 * the sentence "There are five types of Performance: Dance, Passion, Vocals, Visuals,
 * and Composure", so each case below is spelled the way the client spells it and may be
 * printed as-is. Two of them are load-bearing spellings: **Vocals** and **Visuals** are
 * plural, and the fifth is **Composure**, not the "Mental" that GameTora renders from the
 * JP word (`docs/UMAMUSUME_REFERENCE.md` §7 row 48, closed on official copy). A later
 * surface that prints "Vocal" or "Mental" would be printing a guide's word over the
 * client's.
 *
 * The case names and the backing values are deliberately identical: the value is the
 * client's word, so a stored payload reads the same as the enum case and neither needs a
 * translation to be shown. `label()` exists so a reader does not have to know that.
 *
 * **Nothing about magnitude lives here.** What a run starts with, what a training turn
 * pays, what a Lesson costs and how the cap rises are not in this enum and are not
 * defaulted anywhere: `docs/scenarios/07-grand-concert.md` marks every per-level
 * magnitude unverified and this slice models the concept without inventing one.
 */
enum PerformanceType: string
{
    case Dance = 'Dance';
    case Passion = 'Passion';
    case Vocals = 'Vocals';
    case Visuals = 'Visuals';
    case Composure = 'Composure';

    /**
     * The word to print: the client's own, unchanged (see the docblock).
     */
    public function label(): string
    {
        return $this->value;
    }
}
