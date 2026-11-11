import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * Aptitudes on the trainee a spec's run belongs to.
 *
 * The roster seeder stores NULL aptitudes (`UmamusumeSeeder`: no client figure was published for the
 * illustrative rows), and a surface reading a NULL letter prints N/A and makes no judgement
 * (`raceSuitability.ts`). A spec that needs a graded row writes its own letters onto its own run's
 * trainee, the same scratch-database write `race-slots.ts` and `review-candidates.ts` established.
 */

export function setTraineeAptitudes(runId: string, grades: { turf?: string; dirt?: string; medium?: string; mile?: string }): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    const traineeId = db
        .prepare('SELECT umamusume_id FROM training_runs WHERE id = ?')
        .get(Number(runId)) as { umamusume_id: number } | undefined;

    if (traineeId === undefined) {
        throw new Error(`setTraineeAptitudes: run ${runId} has no trainee`);
    }

    db.prepare(
        `UPDATE umamusume SET
            aptitude_turf = ?, aptitude_dirt = ?, aptitude_mile = ?, aptitude_medium = ?
         WHERE id = ?`,
    ).run(
        grades.turf ?? null,
        grades.dirt ?? null,
        grades.mile ?? null,
        grades.medium ?? null,
        traineeId.umamusume_id,
    );
}

export interface SeededGradedSlot {
    id: number;
    title: string;
}

/**
 * A mandatory slot with a graded surface and band, so the planner's mandatory group - the group no
 * position gate can empty - carries a row the aptitude letters can be read from.
 */
export function seedGradedMandatorySlot(): SeededGradedSlot {
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

    try {
        const result = db
            .prepare(
                `INSERT INTO race_catalog_slots
                    (scenario_key, year, month, half, turn, slot_label, title, tier, grade_code,
                     distance, distance_band, surface, track_id, race_id, fans_needed,
                     fans_gain_curve, is_mandatory, is_special_race, did_not_exist, external_ref,
                     sort_order, source_url, snapshot_path, fetched_at, source_timezone, is_manual,
                     created_at, updated_at)
                 VALUES (NULL, 1, 6, 'Late', 12, 'Fixture Graded', 'Fixture Graded Stakes', 'G1', 100,
                     1200, 'Sprint', 'Dirt', 10013, 101301, 0,
                     NULL, 1, 0, NULL, 'fixture-graded-stakes',
                     1, NULL, NULL, NULL, NULL, 0, ?, ?)`,
            )
            .run(now, now);

        return { id: Number(result.lastInsertRowid), title: 'Fixture Graded Stakes' };
    } finally {
        db.close();
    }
}

/** Removes exactly the row `seedGradedMandatorySlot` inserted. */
export function removeGradedMandatorySlot(seeded: SeededGradedSlot): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM race_catalog_slots WHERE id = ?').run(seeded.id);
    } finally {
        db.close();
    }
}
