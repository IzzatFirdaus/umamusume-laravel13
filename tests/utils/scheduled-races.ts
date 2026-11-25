import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * A `Scheduled` race entry, written straight into the suite's scratch database.
 *
 * `Scheduled` is an import-side state: a calendar slot observed in a source without an observed
 * entry decision (`RaceEntryStatus`'s docblock). No rendered surface writes one — `Scheduled` is
 * deliberately absent from `RaceEntryStatus::writable()` — so a spec that needs the state seeds it
 * the way `tests/utils/recorded-races.ts` seeds its completed rows, with the slot and the entry
 * that points at it.
 *
 * KI-173 is what these rows are for: every reader that asks "has the Trainer decided on this
 * race?" must go through `recordsEntry()`, and a `Scheduled` row that still counted there
 * cleared the obligation, emptied the still-to-come strip and greyed a planner row. The browser
 * cases assert what only a rendered page reaches — the badge, the strip rows, the absence of a
 * misleading "Recorded:" line — which `tests/Feature/ScheduledRaceStatusTest.php` cannot see.
 */

export interface SeededScheduledEntry {
    entry_id: number;
    slot_id: number;
    title: string;
}

/**
 * One `Scheduled` entry on `$runId` for a Unity Cup calendar slot at year 1, turn `$turn`,
 * titled `$title`, tied to no turn entry.
 *
 * The entry's `turn_entry_id` stays null on purpose: that column is nullable exactly so "a race
 * the Trainer has not tied to a turn is a complete row" (the column's own migration), and a
 * `Scheduled` row is import-side — observed without an observed decision and without the client's
 * turn log. Writing a turn entry here would also move the run's next turn to play one past the
 * slot (the decision turn derives from the latest logged turn), taking the decision surface the
 * fixture targets off the very turn the slot sits on.
 *
 * The slot is `is_mandatory` with `scenario_key = NULL`, the shared calendar every scenario's
 * readers union in, so the obligation readers the case addresses (`careerGoalSections`,
 * `objectiveSection`, `RaceStripBuilder::$ahead`) all see the row without a per-scenario fixture.
 */
export function seedScheduledRace(runId: number, turn: number, title: string): SeededScheduledEntry {
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

    try {
        const slot = db
            .prepare(
                `INSERT INTO race_catalog_slots
                    (scenario_key, year, month, half, turn, slot_label, title, tier, grade_code,
                     distance, distance_band, surface, track_id, race_id, fans_needed,
                     fans_gain_curve, is_mandatory, is_special_race, did_not_exist, external_ref,
                     sort_order, source_url, snapshot_path, fetched_at, source_timezone, is_manual,
                     created_at, updated_at)
                 VALUES (NULL, 1, 6, 'Late', ?, 'Late June', ?, 'G1', 100,
                         1600, 'Mile', 'Turf', 10008, 100801, 4500,
                         21, 1, 0, 0, ?,
                         ?, 'fixture://scheduled-race', NULL, ?, 'UTC', 0,
                         ?, ?)`,
            )
            .run(turn, title, `scheduled-race-${turn}`, turn, now, now, now);
        const slotId = Number(slot.lastInsertRowid);

        const entry = db
            .prepare(
                `INSERT INTO race_entries
                    (training_run_id, race_catalog_slot_id, status, placement, turn_entry_id, created_at, updated_at)
                 VALUES (?, ?, 'Scheduled', NULL, ?, ?, ?)`,
            )
            .run(runId, slotId, null, now, now);

        return { entry_id: Number(entry.lastInsertRowid), slot_id: slotId, title };
    } finally {
        db.close();
    }
}

/**
 * The seeded catalogue row and entry, removed together so a rerun starts clean.
 *
 * The delete retries a few times before giving up: the suite's `php artisan serve` holds the
 * scratch database in WAL mode, and a write the server has not checkpointed can answer
 * `SQLITE_BUSY` ("database is locked") to a second connection for as long as its own transaction
 * stays open. A teardown that dies on that would leave the fixture row behind and fail a case
 * whose assertions all passed.
 */
export function removeScheduledRace(seeded: SeededScheduledEntry): void {
    for (let attempt = 0; attempt < 10; attempt++) {
        const db = new DatabaseSync(resolvedScratchDatabase());

        try {
            db.exec('PRAGMA busy_timeout = 2000');
            db.prepare('DELETE FROM race_entries WHERE id = ?').run(seeded.entry_id);
            db.prepare('DELETE FROM race_catalog_slots WHERE id = ?').run(seeded.slot_id);

            return;
        } catch (error) {
            if (attempt === 9 || !(error instanceof Error) || !error.message.includes('database is locked')) {
                throw error;
            }
        } finally {
            db.close();
        }
    }
}
