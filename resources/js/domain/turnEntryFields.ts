/**
 * The five stat fields a turn entry records, from one source.
 *
 * `runs.turns.store` and its `StoreTurnEntryRequest` already own the write. This owns the field list and
 * the labels the forms render, which were declared separately on the run record, the training decision
 * and the cockpit correction, and drifted apart (`Speed *`, `Speed total *`, `Speed`).
 */
export const TURN_ENTRY_STAT_FIELDS = [
    { name: 'speed', label: 'Speed' },
    { name: 'stamina', label: 'Stamina' },
    { name: 'power', label: 'Power' },
    { name: 'guts', label: 'Guts' },
    { name: 'wit', label: 'Wit' },
] as const;
