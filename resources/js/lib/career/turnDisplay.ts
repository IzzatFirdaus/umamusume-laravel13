/*
 * The one derivation of "which turn is this run on", for the surfaces that label an undo and the
 * surfaces that prefill the next turn (R7-27, R7-32).
 *
 * Four screens each spelled this themselves, from four different sources, and the walk read the
 * disagreement: `Undo turn 1` on a one-turn run (Training detail, from `write.turn`), `Undo turn 19`
 * on the Races screen (the turn *within* the year, not the career's own turn index), and
 * `Undo turn 67` on the Timeline (the rail's own maximum). The Events and Inheritance forms
 * prefilled 68 while the Timeline labelled 67, so the two halves of one convention printed
 * neighbouring numbers and neither could be trusted.
 *
 * A caller passes what it actually holds: `turnEntries` (a logged-turn list, any shape with a
 * `turn`), and `career_position` where its payload carries one. Both are optional because no single
 * payload has both; a caller with neither still gets the first turn rather than `NaN` or `0`.
 *
 * `latestLoggedTurn` is the turn an undo would remove. Its floor is 1, the first turn of a career:
 * a label naming turn 0 names a turn no career has.
 *
 * `nextTurn` is the turn a form records on. With a log it is one past the last one. With nothing
 * logged it is the turn the run stands on - the imported position for a snapshot, which is the turn
 * the Trainer is playing, or 1 for a career that has not started. It is deliberately not
 * `latestLoggedTurn + 1` in that case: the floor 1 is not a logged fact, and adding to it would
 * prefill turn 2 for a run whose first turn is still turn 1.
 */

/** The run shape the two functions read. Both keys are optional: no payload carries both. */
export interface TurnRun {
    /** The run's logged turns, in any order. A page holding only `entries` passes those. */
    turnEntries?: { turn: number }[] | undefined;
    /** The stored or derived position, where the payload carries one. */
    career_position?: { turn_index: number } | null | undefined;
}

/** The highest logged turn, or the run's position when nothing is logged, or the first turn. */
export function latestLoggedTurn(run: TurnRun): number {
    const logged = run.turnEntries ?? [];

    if (logged.length > 0) {
        return Math.max(...logged.map((entry) => entry.turn));
    }

    return run.career_position?.turn_index ?? 1;
}

/**
 * The turn the next entry lands on: one past whatever the run last knew about itself.
 *
 * R7-32. `TrainingRun::nextTurnNumber()` is the server's one owner of this number and it answers
 * `max(turn) + 1` when anything is logged, else `career_position.turnIndex + 1`, else 1. The `+1` is
 * not decoration on the logged branch: a snapshot standing at Senior Early October (turn 67) with no
 * complete turn entry opens the next turn at 68, and answering 67 would prefill the very turn the
 * snapshot already recorded. An earlier version of this helper dropped the `+1` on the position
 * branch, which put the Events and Inheritance forms one turn behind TrainingDetail on exactly the
 * snapshot state the position fallback exists to serve.
 */
export function nextTurn(run: TurnRun): number {
    const logged = run.turnEntries ?? [];

    const latest = latestLoggedTurn(run);

    // `latestLoggedTurn` already answers the position (or 1) when nothing is logged, so the position
    // branch adds the step. Only a run with neither a log nor a position stops at 1, because turn 1 is
    // the first turn of a career and not one past anything.
    if (logged.length > 0) {
        return latest + 1;
    }

    return run.career_position === null || run.career_position === undefined ? 1 : latest + 1;
}

/**
 * The turn an undo would remove, given the turn the caller is standing on.
 *
 * The floor is 1, the first turn of a career: a label naming turn 0 names a turn no career has, which
 * is the reason `Math.max(1, ...)` was spelled out at every call site this replaces.
 */
export function undoTurnLabel(currentTurn: number): number {
    return Math.max(1, currentTurn - 1);
}
