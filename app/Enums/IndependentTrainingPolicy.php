<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The three training policies a Trainer fixes before starting an Independent Training run
 * (`UMAMUSUME_REFERENCE.md` §1.1.7, §1.6.8).
 *
 * The backing values are the storage keys; the labels are the `[Global]` client nouns, whose Japanese
 * originals the reference carries (バランス重視 / スタミナ重視 / スプリント重視). Kamigame recommends
 * Balanced because the Stamina and Sprint policies skew stats and cost win rate, which is the
 * recommendation the config form renders as a note.
 */
enum IndependentTrainingPolicy: string
{
    case Balanced = 'balanced';
    case Stamina = 'stamina';
    case Sprint = 'sprint';

    /**
     * The client noun to print. `Sprint focus` and `Stamina focus` are the reference's own renderings
     * of the JP terms, not a third-party translation.
     */
    public function label(): string
    {
        return match ($this) {
            self::Balanced => 'Balanced',
            self::Stamina => 'Stamina focus',
            self::Sprint => 'Sprint focus',
        };
    }
}