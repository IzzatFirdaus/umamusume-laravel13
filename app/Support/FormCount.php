<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CharacterCard;
use Illuminate\Support\Collection;

/**
 * How many costume forms a surface holds, and what that count is a count *of*.
 *
 * R7-02. Three surfaces scope `character_cards` differently, and each is right for its own job:
 * the picker offers only forms that carry a Global release date (a form the client does not carry
 * cannot be chosen for a career), while the profile and the catalog show every confirmed form (a
 * read screen hides nothing). Walk 7 read `2 forms` on one screen and three on another and filed the
 * difference as a contradiction; both counts were correct, and neither said what it counted.
 *
 * This class is the single owner of the sentence. `label()` takes the cards a surface already holds
 * and splits them on the release date, so adding a form or backfilling a date updates every label
 * without a code change — and a surface whose list is pre-filtered states that scope rather than
 * claiming a total.
 */
final class FormCount
{
    /**
     * The label for a surface that offers only released forms.
     *
     * The picker's own list is already filtered to `global_release_date IS NOT NULL`, so every card
     * handed here is released; the count is stated as released-only so it cannot be read as a total.
     *
     * @param  Collection<int, CharacterCard>  $cards
     */
    public static function releasedLabel(Collection $cards): string
    {
        $count = $cards->count();

        return $count === 1 ? '1 released form' : $count.' released forms';
    }

    /**
     * The label for a surface that lists every confirmed form and splits them by release state.
     *
     * @param  Collection<int, CharacterCard>  $cards
     */
    public static function splitLabel(Collection $cards): string
    {
        $released = $cards->filter(static fn (CharacterCard $card): bool => $card->global_release_date !== null)->count();
        $undated = $cards->count() - $released;
        $total = $cards->count();

        if ($undated === 0) {
            return $total === 1 ? '1 form (released)' : $total.' forms (all released)';
        }

        $formWord = $total === 1 ? 'form' : 'forms';

        return $total.' '.$formWord.' ('.$released.' released, '.$undated.' undated)';
    }

    /**
     * The label for a surface whose list is pre-filtered to released forms.
     *
     * Name kept short because the call sites read better: `FormCount::label($cards)`.
     *
     * @param  Collection<int, CharacterCard>  $cards
     */
    public static function label(Collection $cards): string
    {
        return self::releasedLabel($cards);
    }
}
