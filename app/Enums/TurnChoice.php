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
}
