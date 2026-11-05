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
            (name, name_ja, match_key, match_key_ja, sp_cost, type, is_unique, created_at, updated_at,
             export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
             fetched_at, source_timezone, is_manual, condition_groups, description,
             description_match_key, distance_type)
         VALUES (?, NULL, ?, NULL, 100, 'Speed', 0, ?, ?, ?, 1, 'GlobalReleased', 1,
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

/**
 * The independent per-status counts `uma:skill-inventory` computes for this scratch DB, keyed by the
 * three buckets the catalogue header names.
 *
 * Same cross-check D6 uses for the range-operator footer, and for the same reason: the header's
 * figures come through `SkillController::releaseStatusCounts()`, so asserting them against that
 * method would only prove the value was passed through. The inventory command groups the same
 * `release_status` column through its own code path.
 *
 * `other` is derived rather than read: the command prints one line per enum case
 * (`table.release_status.<Case>=N`), and only the two named cases have a line of their own. The
 * remainder is what the header calls `other`, computed here from the same lines so the two sides
 * still rest on independent code.
 */
export function inventoryReleaseStatusCounts(): { global: number; japan_only: number; other: number } {
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

    const total = output.match(/^table\.total=(\d+)$/m);
    const global = output.match(/^table\.release_status\.GlobalReleased=(\d+)$/m);
    const japanOnly = output.match(/^table\.release_status\.JapanOnly=(\d+)$/m);

    if (total === null || global === null || japanOnly === null) {
        throw new Error(`uma:skill-inventory printed no table.release_status lines:\n${output}`);
    }

    return {
        global: Number(global[1]),
        japan_only: Number(japanOnly[1]),
        other: Number(total[1]) - Number(global[1]) - Number(japanOnly[1]),
    };
}

const JAPAN_ONLY_EXPORT_ID = 990010;

/** Seeds one JapanOnly skill that the Global-scoped picker must never offer. */
export function seedJapanOnlySkill(name = 'Japan Only Fixture'): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

        db.prepare(
            `INSERT INTO skills
                (name, name_ja, match_key, match_key_ja, sp_cost, type, is_unique, created_at, updated_at,
                 export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
                 fetched_at, source_timezone, is_manual, condition_groups, description,
                 description_match_key, distance_type)
             VALUES (?, NULL, ?, NULL, 120, 'Speed', 0, ?, ?, ?, 1, 'JapanOnly', 1,
                     'https://gametora.test/skills.json', NULL, ?, 'Asia/Tokyo', 0, NULL, NULL, NULL, NULL)`,
        ).run(name, name.toLowerCase().replace(/\s+/g, ''), now, now, JAPAN_ONLY_EXPORT_ID, now);
    } finally {
        db.close();
    }
}

/** Removes the fixture inserted by `seedJapanOnlySkill()`. */
export function removeJapanOnlySkill(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM skills WHERE export_id = ?').run(JAPAN_ONLY_EXPORT_ID);
    } finally {
        db.close();
    }
}

const NO_DESCRIPTION_EXPORT_ID = 990011;

/** Seeds one Global skill with a NULL description, so the "No client string." state is reachable. */
export function seedNoDescriptionSkill(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

        db.prepare(
            `INSERT INTO skills
                (name, name_ja, match_key, sp_cost, type, is_unique, created_at, updated_at,
                 export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
                 fetched_at, source_timezone, is_manual, condition_groups, description,
                 description_match_key, distance_type)
             VALUES ('No String Fixture', NULL, 'nostringfixture', 150, 'Speed', 0, ?, ?, ?, 1,
                     'GlobalReleased', 1, 'https://gametora.test/skills.json', NULL, ?, 'Asia/Tokyo',
                     0, NULL, NULL, NULL, NULL)`,
        ).run(now, now, NO_DESCRIPTION_EXPORT_ID, now);
    } finally {
        db.close();
    }
}

/** Removes the fixture inserted by `seedNoDescriptionSkill()`. */
export function removeNoDescriptionSkill(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM skills WHERE export_id = ?').run(NO_DESCRIPTION_EXPORT_ID);
    } finally {
        db.close();
    }
}

const SCOPE_JAPAN_EXPORT_ID = 990021;
const SCOPE_GLOBAL_EXPORT_ID = 990022;

/**
 * Seeds the scope filter's two fixtures: one JapanOnly row whose only name is the source's Japanese
 * string, and one Global row the client named.
 *
 * The shapest thing here is `name_ja` on the JP row. `seedJapanOnlySkill()` above sets it NULL and
 * leaves `name_is_client` at 1 — wrong for these assertions twice over: ruling A shows `name_ja` on a
 * JP row, and `name_is_client` is `$clientName !== null && $onGlobal` by construction, so it cannot be
 * true on a row the client has not shipped. These rows are therefore separate from that fixture rather
 * than a change to it, because D6's range-operator test depends on the older shape.
 */
export function seedScopeFixtures(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

        db.prepare(
            `INSERT INTO skills
                (name, name_ja, match_key, match_key_ja, sp_cost, type, is_unique, created_at, updated_at,
                 export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
                 fetched_at, source_timezone, is_manual, condition_groups, description,
                 description_match_key, distance_type)
             VALUES ('Raise My Soul''s Blade!', '掲げよ、己が魂の剣譜！', 'raisemysoulsblade',
                     '掲げよ、己が魂の剣譜!', 180, 'Speed', 0,
                     ?, ?, ?, 1, 'JapanOnly', 0, 'https://gametora.test/skills.json', NULL,
                     ?, 'Asia/Tokyo', 0, NULL, 'The source states an English sentence for this row.',
                     'thesourcestatesanenglishsentenceforthisrow', NULL)`,
        ).run(now, now, SCOPE_JAPAN_EXPORT_ID, now);

        db.prepare(
            `INSERT INTO skills
                (name, name_ja, match_key, match_key_ja, sp_cost, type, is_unique, created_at, updated_at,
                 export_id, rarity, release_status, name_is_client, source_url, snapshot_path,
                 fetched_at, source_timezone, is_manual, condition_groups, description,
                 description_match_key, distance_type)
             VALUES ('Client Named Row', NULL, 'clientnamedrow', NULL, 160, 'Speed', 0,
                     ?, ?, ?, 1, 'GlobalReleased', 1, 'https://gametora.test/skills.json', NULL,
                     ?, 'Asia/Tokyo', 0, NULL, 'A client sentence for the Global row.',
                     'aclientsentencefortheglobalrow', NULL)`,
        ).run(now, now, SCOPE_GLOBAL_EXPORT_ID, now);
    } finally {
        db.close();
    }
}

/** Removes both fixtures inserted by `seedScopeFixtures()`. */
export function removeScopeFixtures(): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM skills WHERE export_id IN (?, ?)').run(SCOPE_JAPAN_EXPORT_ID, SCOPE_GLOBAL_EXPORT_ID);
    } finally {
        db.close();
    }
}
