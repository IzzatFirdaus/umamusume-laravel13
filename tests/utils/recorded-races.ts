import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * Recorded race entries, written straight into the suite's scratch database.
 *
 * N3.3's bound is five, and the only rendered way to record a race is one POST per row on
 * `/training-runs/{run}/races` - six of those per case is a fixture that costs more than the
 * assertion. `tests/utils/race-slots.ts` set the precedent for seeding this file from a spec, and this
 * follows it with the other half of the pair: the calendar row plus the entry that points at it.
 *
 * The rows are shaped like `RaceCatalogSlotFactory` / `RaceEntryFactory`, including the `turn_entries`
 * link the strip reads for the row's turn (`RaceStripBuilder::stripRow()`), because a bound asserted on
 * rows that sort by turn has to carry turns to sort by.
 */
export interface SeededRaceEntry {
    entry_id: number;
    slot_id: number;
    title: string;
}

/**
 * `$count` recorded races on `$runId`, at turns 1..`$count`, with `($index)`-th title.
 *
 * Returns the seeded rows in insertion order so a case can name the one it expects to survive the
 * bound - which is the last one, since the strip keeps the tail.
 */
export function seedRecordedRaces(runId: number, count: number, labelPrefix = 'Bounded race'): SeededRaceEntry[] {
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);
    const seeded: SeededRaceEntry[] = [];

    try {
        // The run's own logged turns, if the case did not log enough of them. The strip reads
        // `turnEntries.turn` for each row, so a race with no turn behind it prints the KI-17 absence
        // rather than a number - which would make the bound's tail read as unordered.
        const logged = db
            .prepare('SELECT turn FROM turn_entries WHERE training_run_id = ? ORDER BY turn')
            .all(runId) as { turn: number }[];

        for (let index = 1; index <= count; index++) {
            if (!logged.some((row) => row.turn === index)) {
                db.prepare(
                    `INSERT INTO turn_entries
                        (training_run_id, turn, speed, stamina, power, guts, wit, sp, energy, mood, fans, created_at, updated_at)
                        VALUES (?, ?, 600, 600, 600, 600, 600, 120, 60, 'NORMAL', 1000, ?, ?)`,
                ).run(runId, index, now, now);
            }
        }

        for (let index = 1; index <= count; index++) {
            const title = `${labelPrefix} ${index}`;
            const slot = db
                .prepare(
                    `INSERT INTO race_catalog_slots
                        (scenario_key, year, month, half, turn, slot_label, title, tier, grade_code,
                         distance, distance_band, surface, track_id, race_id, fans_needed,
                         fans_gain_curve, is_mandatory, is_special_race, did_not_exist, external_ref,
                         sort_order, source_url, snapshot_path, fetched_at, source_timezone, is_manual,
                         created_at, updated_at)
                     VALUES ('unity_cup', 1, ?, 'Early', ?, 'Early', ?, 'G1', 100,
                             1600, 'Mile', 'Turf', 10008, 100801, 4500,
                             21, 0, 0, 0, ?,
                             ?, 'fixture://race-strip', NULL, ?, 'UTC', 0,
                             ?, ?)`,
                ).run(index, index, title, `race-strip-${index}`, index, now, now, now);
            const slotId = Number(slot.lastInsertRowid);

            const turn = db.prepare('SELECT id FROM turn_entries WHERE training_run_id = ? AND turn = ?').get(runId, index) as { id: number };
            const entry = db
                .prepare(
                    `INSERT INTO race_entries
                        (training_run_id, race_catalog_slot_id, status, placement, turn_entry_id, created_at, updated_at)
                     VALUES (?, ?, 'Completed', 1, ?, ?, ?)`,
                )
                .run(runId, slotId, turn.id, now, now);

            seeded.push({ entry_id: Number(entry.lastInsertRowid), slot_id: slotId, title });
        }
    } finally {
        db.close();
    }

    return seeded;
}

/** The seeded catalogue rows and entries, removed together so a rerun starts clean. */
export function removeRecordedRaces(seeded: SeededRaceEntry[]): void {
    if (seeded.length === 0) {
        return;
    }

    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        for (const row of seeded) {
            db.prepare('DELETE FROM race_entries WHERE id = ?').run(row.entry_id);
            db.prepare('DELETE FROM race_catalog_slots WHERE id = ?').run(row.slot_id);
        }
    } finally {
        db.close();
    }
}