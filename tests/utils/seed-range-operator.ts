import { DatabaseSync } from 'node:sqlite';
import { execSync } from 'node:child_process';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { resolvedScratchDatabase } from '../browser/global-setup';

const repoRoot = join(dirname(fileURLToPath(import.meta.url)), '..', '..');

/*
 * Seed helpers for the skill-catalogue range-operator D6 test.
 *
 * The scratch DB holds the seeded reference catalogue, which has 0 range operators.
 * To prove the footer count is COMPUTED (not a placeholder), this inserts one
 * range-operator skill and two exact-token skills directly into the scratch file,
 * then removes them afterwards. Follows the `tests/utils/race-slots.ts` precedent.
 */

export interface SeededSkill {
    id: number;
    exportId: number;
    name: string;
}

const RANGE_EXPORT_ID = 990001;
const EXACT_EXPORT_IDS = [990002, 990003];

function insertSkill(
    db: DatabaseSync,
    exportId: number,
    name: string,
    condition: string,
    distanceType: number | null,
): number {
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);
    const conditionGroups = JSON.stringify([
        {
            base_time: 50000,
            condition,
            precondition: '',
            effects: [{ type: 27, value: 100 }],
        },
    ]);

    db.prepare(
        `INSERT INTO skills
            (name, name_ja, match_key, sp_cost, type, is_unique, created_at, updated_at,
             export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
             fetched_at, source_timezone, is_manual, condition_groups, description,
             description_match_key, distance_type)
         VALUES (?, NULL, ?, 100, 'Speed', 0, ?, ?, ?, 1, 'GlobalReleased', 1,
                 'https://gametora.test/skills.json', NULL, ?, 'Asia/Tokyo', 0, ?, NULL, NULL, ?)`,
    ).run(
        name,
        name.toLowerCase().replace(/\s+/g, ''),
        now,
        now,
        exportId,
        now,
        conditionGroups,
        distanceType,
    );

    const row = db.prepare('SELECT id FROM skills WHERE export_id = ?').get(exportId) as { id: number };

    return row.id;
}

/** Seeds one range-operator skill (distance_type<=2) and two exact-token controls. */
export function seedRangeOperatorSkills(): SeededSkill[] {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const rangeId = insertSkill(db, RANGE_EXPORT_ID, 'Fixture Range Operator', 'distance_type<=2', null);
        const exactA = insertSkill(db, EXACT_EXPORT_IDS[0], 'Fixture Exact A', 'distance_type==2', 2);
        const exactB = insertSkill(db, EXACT_EXPORT_IDS[1], 'Fixture Exact B', 'distance_type==2', 2);

        return [
            { id: rangeId, exportId: RANGE_EXPORT_ID, name: 'Fixture Range Operator' },
            { id: exactA, exportId: EXACT_EXPORT_IDS[0], name: 'Fixture Exact A' },
            { id: exactB, exportId: EXACT_EXPORT_IDS[1], name: 'Fixture Exact B' },
        ];
    } finally {
        db.close();
    }
}

/** Removes the fixture skills inserted by `seedRangeOperatorSkills()`. */
export function removeRangeOperatorSkills(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM skills WHERE export_id IN (?, ?, ?)').run(
            RANGE_EXPORT_ID,
            ...EXACT_EXPORT_IDS,
        );
    } finally {
        db.close();
    }
}

/**
 * The independent range-operator count `uma:skill-inventory` computes for this scratch DB.
 *
 * Deliberately not `SkillController::rangeOperatorCount()`: that method is the thing the footer
 * test exists to verify, so checking the footer against it would only prove the value was passed
 * through. The inventory command parses the same `condition_groups` column through its own code
 * path, which is the cross-check D6 asks for.
 */
export function inventoryRangeOpCount(): number {
    const db = resolvedScratchDatabase();
    const output = execSync('php artisan uma:skill-inventory', {
        cwd: repoRoot,
        encoding: 'utf8',
        env: {
            ...process.env,
            DB_DATABASE: db,
            UMA_DATABASE_ROLE: 'browser',
            UMA_EXPECTED_DATABASE: db,
        },
    });

    const match = output.match(/^table\.range_op_count_global=(\d+)$/m);

    if (match === null) {
        throw new Error(`uma:skill-inventory printed no table.range_op_count_global line:\n${output}`);
    }

    return Number(match[1]);
}
