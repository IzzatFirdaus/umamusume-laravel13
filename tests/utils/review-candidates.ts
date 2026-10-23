import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * Pending review candidates, written straight into the suite's scratch database.
 *
 * The review queue is populated by the match pipeline (`uma:fetch`), which needs the network, so a
 * seeded scratch database holds none: without a fixture every case in `review.spec.ts` skips, and a
 * skip proves nothing about the page. `tests/utils/ura-completed-run.ts` set the precedent for
 * touching the scratch file directly from a spec (the database is the harness's own, built and owned
 * by `global-setup.ts`), and this follows it.
 *
 * The row shape is the factory's (`database/factories/MatchCandidateFactory.php`) and the columns are
 * the migration's. `proposed_match_key` is derived by the model's `newFactory()` in a real write; it
 * is nullable, so a fixture leaves it null rather than re-implementing the normaliser.
 */

export interface SeededCandidate {
    id: number;
    proposedName: string;
}

/** The catalog id of a seeded trainee, so a fuzzy row can point at a real catalog entry. */
function firstTraineeId(db: DatabaseSync): number | null {
    const row = db.prepare('SELECT id FROM umamusume ORDER BY id LIMIT 1').get() as { id: number } | undefined;

    return row?.id ?? null;
}

/**
 * Insert `count` pending candidates and answer their ids.
 *
 * `suggested` puts the row in the fuzzy half (a suggestion the Trainer can accept in one click);
 * otherwise the row is the no-match half (the engine found nothing to propose).
 */
export function seedPendingCandidates(count: number, options: { suggested?: boolean; prefix?: string } = {}): SeededCandidate[] {
    const { suggested = false, prefix = 'Fixture' } = options;
    const db = new DatabaseSync(resolvedScratchDatabase());
    const traineeId = suggested ? firstTraineeId(db) : null;
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);
    const seeded: SeededCandidate[] = [];

    try {
        for (let index = 1; index <= count; index++) {
            const name = `${prefix} Trainee ${index}`;
            const result = db
                .prepare(
                    `INSERT INTO match_candidates
                        (source_key, external_ref, proposed_name, proposed_name_ja, proposed_match_key,
                         suggested_umamusume_id, match_tier, status, payload, created_by_fetch_at, created_at, updated_at)
                     VALUES (?, NULL, ?, ?, NULL, ?, ?, 'Pending', ?, ?, ?, ?)`,
                )
                .run(
                    'fixture',
                    name,
                    suggested ? `${name} JP` : null,
                    suggested ? traineeId : null,
                    suggested && traineeId !== null ? 'Fuzzy' : 'None',
                    JSON.stringify({ name }),
                    now,
                    now,
                    now,
                );

            seeded.push({ id: Number(result.lastInsertRowid), proposedName: name });
        }

        return seeded;
    } finally {
        db.close();
    }
}

/** Remove exactly the rows this fixture wrote, so the queue is left as the suite found it. */
export function clearPendingCandidates(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        /*
         * Every row, not only this fixture's. The queue is populated by the match pipeline, which no
         * spec runs, so a row in this table during a browser run is a fixture row by construction —
         * and leaving another case's rows behind keeps the rowid high-water mark high, which is what
         * makes a literal detail URL impossible (see the sequence reset below).
         */
        db.prepare('DELETE FROM match_candidates').run();

        /*
         * Reset the table's rowid high-water mark, so the next fixture insert gets id 1 again.
         *
         * Without this the ids climb monotonically across a run (the migration declares
         * `primary key autoincrement`, so SQLite never reuses a rowid), and a spec cannot name a
         * detail URL literally — which `RouteCoverageTest` requires: it reads `page.goto('<literal>')`
         * from the spec source to decide which routes the corpus visits and scans. A reproducible id
         * is what lets `/review/1` be the row a fixture just wrote.
         *
         * `sqlite_sequence` only exists once an autoincrement table has held a row, so its absence is
         * not an error.
         */
        db.prepare("DELETE FROM sqlite_sequence WHERE name = 'match_candidates'").run();
    } catch {
        // A scratch database without the sequence table yet: nothing to reset.
    } finally {
        db.close();
    }
}
