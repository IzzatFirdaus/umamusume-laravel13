<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How confident the Trainer is in one snapshot field, which is not the same question as whether the
 * field has a value.
 *
 * The review screen's whole purpose is to keep these three apart: "I read a number" (Known), "I looked
 * and the client does not show it" (Unknown) and "I did not fill this in" (Not provided) are three
 * different statements about a career, and collapsing them into one blank would lose the difference
 * the Trainer took the trouble to mark. The backed values are the wire vocabulary the form posts and
 * the JSON store keeps.
 */
enum SnapshotFieldState: string
{
    case Known = 'known';
    case Unknown = 'unknown';
    case NotProvided = 'not_provided';

    /**
     * The word the review screen prints before the field's own label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Known => 'Known',
            self::Unknown => 'Unknown',
            self::NotProvided => 'Not provided',
        };
    }
}
