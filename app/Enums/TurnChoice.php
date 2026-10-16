<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The action a Trainer took on a turn, as one stored word.
 *
 * **These are the application's own literals, read off the two places that already produce them.** The
 * guided training rail posts `training-<Stat>` per entry of `config('scenarios.stat_order')`
 * (`Career\TrainingDecisionController::optionSection()`, reaching the request through `train()` in
 * `Career\TrainingDetail.vue`), and `TrainingRunController::turnChoices()` declares the two non-training
 * keys: `rest`, and `mood` for the action the client calls Recreation. `mood` keeps the key the rail gave
 * it even though the screen says Recreation, because rows already written by a failure or Performance
 * event are looked up by that key (`TrainingRunController::choiceLabel()`).
 *
 * **No case exists for a duel, a race, an event or a shop trip**, because no write path produces one: a
 * race is a `race_entries` row, an event or purchase a `turn_events` row, and neither posts `choice`. The
 * duels of the URA finale are display-only in this build (ruling D-5).
 */
enum TurnChoice: string
{
    case TrainSpeed = 'training-Speed';
    case TrainStamina = 'training-Stamina';
    case TrainPower = 'training-Power';
    case TrainGuts = 'training-Guts';
    case TrainWit = 'training-Wit';
    case Rest = 'rest';
    case Recreation = 'mood';

    /** The word the client shows, Global vocabulary only (`AGENTS.md` §13). */
    public function label(): string
    {
        return match ($this) {
            self::TrainSpeed => 'Speed',
            self::TrainStamina => 'Stamina',
            self::TrainPower => 'Power',
            self::TrainGuts => 'Guts',
            self::TrainWit => 'Wit',
            self::Rest => 'Rest',
            self::Recreation => 'Recreation',
        };
    }

    /**
     * The outcome words this choice offers, and whether each one names a failure.
     *
     * **The branch rule is the corpus's, not this enum's.** D-200 gives every choice a success branch and
     * a failure branch unless it can be stated that the choice cannot fail; D-201 makes a failure a
     * first-class log entry rather than an error state, naming a mood drop among them; and D-205 rules
     * that Rest carries the same branch treatment as any other choice
     * (`docs/research-scratch/DESIGN-CORPUS.md`). Rest's failure branch is the reference's own under-slept
     * tier, `+30 (failure, sometimes with 夜ふかし気味 / Late-Night Feeling)`
     * (`UMAMUSUME_REFERENCE.md` §1.1.5), and `Fell` names a mood direction the sources do not
     * document as a Recreation failure (Gamerant 2025-07-27; the Japanese action guide states
     * recreation produces at least one stage of mood-up, not a downward branch), so it flags false
     * like `Lifted` and `No Change`.
     *
     * **No word here is a client string, and none claims a number.** The pair the rail already stores is
     * this application's wording and these are the same kind of word; the client's own vocabulary for a
     * bad rest is the Late-Night Feeling condition, which is a state the run carries, not an outcome of
     * the turn. Nothing in the map prices a tier: `ADR-0001` §3 keeps a published figure off a forecast
     * and this keeps one off a record.
     *
     * @return array<string, bool> the outcome word, keyed, against whether it is a failure
     */
    public function outcomes(): array
    {
        return match ($this) {
            self::Rest => ['Success' => false, 'Poor Rest' => true],
            self::Recreation => ['Lifted' => false, 'No Change' => false, 'Fell' => false],
            default => ['Success' => false, 'Failure' => true],
        };
    }
}
