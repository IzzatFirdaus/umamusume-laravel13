import { DatabaseSync } from 'node:sqlite';
import { scratchDatabasePath } from '../browser/global-setup';

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
    const db = new DatabaseSync(scratchDatabasePath());
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
    const db = new DatabaseSync(scratchDatabasePath());

    try {
        db.prepare("DELETE FROM match_candidates WHERE source_key = 'fixture'").run();
    } finally {
        db.close();
    }
}
