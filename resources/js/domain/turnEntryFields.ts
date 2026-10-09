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

/**
 * Energy's three states, and the three bands. The control renders a number for `exact`, a band select
 * for `band`, and the named absence for `unknown`, so the three readings stay apart on the screen the
 * way they are kept apart in the store.
 */
export const TURN_ENTRY_ENERGY_STATES = [
    { value: 'exact', label: 'Exact' },
    { value: 'band', label: 'Band' },
    { value: 'unknown', label: 'Not recorded' },
] as const;

export const TURN_ENTRY_ENERGY_BANDS = [
    { value: 'low', label: 'Low' },
    { value: 'mid', label: 'Mid' },
    { value: 'high', label: 'High' },
] as const;

/**
 * The five facility levels a turn was trained at, one per stat.
 *
 * Derived rather than written out: the client's ladder is per stat and its labels are the stat labels,
 * so a second literal list would be a second place for the two to drift. `TurnEntryFieldSourceTest`
 * pins `TURN_ENTRY_STAT_FIELDS` by matching quoted `{ name, label }` pairs in this file, and a literal
 * list here would double that match; the derived form keeps one owner and keeps the pin exact.
 */
export const TURN_ENTRY_FACILITY_FIELDS = TURN_ENTRY_STAT_FIELDS.map((field) => ({
    name: `facility_${field.name}`,
    label: field.label,
}));
