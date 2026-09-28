<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The six states of a teammate's Spirit Burst (D-223, Unity Cup).
 *
 * Case values are this tool's identifiers, not client strings: no Global English
 * capture names these states, so nothing may render one of these values as if the
 * client had written it (D-20). A display slice owes a label map beside this enum.
 *
 * The machine is Chargeable -> charged -> held (charged and deliberately untriggered)
 * -> normal burst spent -> Extreme chargeable -> Extreme spent. "Spent" is never
 * terminal: the 2026-07-01 update made an Extreme burst available on the teammate's
 * next Unity Training after their normal burst, which is what invalidated the
 * pre-patch "one Spirit Burst per career" rule. A model that treated ExtremeSpent as
 * an end state would be re-asserting a rule the game no longer has.
 */
enum SpiritBurstState: string
{
    case Chargeable = 'Chargeable';
    case Charged = 'Charged';
    case Held = 'Held';
    case NormalBurstSpent = 'NormalBurstSpent';
    case ExtremeChargeable = 'ExtremeChargeable';
    case ExtremeSpent = 'ExtremeSpent';

    /**
     * The one sentence every reader of a stored payload needs: a spent teammate is
     * still on the machine, not off it.
     */
    public function isSpent(): bool
    {
        return $this === self::NormalBurstSpent || $this === self::ExtremeSpent;
    }

    /**
     * What a Trainer reads (KI-18). The backing values above are storage identifiers and
     * the docblock says no client capture names these states, so a surface that prints a
     * value is presenting this tool's identifier as the game's word (D-20).
     *
     * These words describe a machine this corpus documents; they are not claimed as
     * Global client copy, and nothing here may be diffed against a capture as if it were.
     */
    public function label(): string
    {
        return match ($this) {
            self::Chargeable => 'Chargeable',
            self::Charged => 'Charged',
            self::Held => 'Charged, held',
            self::NormalBurstSpent => 'Burst spent',
            self::ExtremeChargeable => 'Extreme chargeable',
            self::ExtremeSpent => 'Extreme spent',
        };
    }
}
