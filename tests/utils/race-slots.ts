import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * Race slots in the suite's scratch database.
 *
 * No seeder fills `race_catalog_slots`: the calendar arrives through `uma:fetch`, which needs the
 * network, so a seeded scratch database holds none and every populated assertion on `/database/races`
 * would branch to the offline state. `tests/utils/review-candidates.ts` set the precedent for writing
 * the harness's own scratch file from a spec, and this follows it.
 *
 * The rows are shaped like `RaceCatalogSlotFactory`, and the 1400 m row carries the band the parser
 * derives (`GametoraRaceCatalogParser::band()`: Sprint to 1400 m), so the screen's disputed-boundary
 * marking is exercised without re-deriving the band here.
 */

export interface SeededRaceSlot {
    id: number;
    title: string;
}

/** One full row, one disputed 1400 m row with no fan gate, and one debut with no course figures. */
export function seedRaceSlots(): SeededRaceSlot[] {
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date().toISOString().replace('T', ' ').slice(0, 19);
    const rows = [
        {
            year: 1, month: 6, half: 'Late', turn: 12, slot_label: 'Late June',
            title: 'Fixture Debut', tier: 'Debut', grade_code: 900, distance: null,
            distance_band: null, surface: null, track_id: null, race_id: null,
            fans_needed: null, fans_gain_curve: null, is_mandatory: 1, is_special_race: 1,
            external_ref: 'fixture-debut', sort_order: 2,
        },
        {
            year: 2, month: 4, half: 'Early', turn: 7, slot_label: 'Early April',
            title: 'Fixture Stakes', tier: 'G1', grade_code: 100, distance: 1600,
            distance_band: 'Mile', surface: 'Turf', track_id: 10008, race_id: 100801,
            fans_needed: 4500, fans_gain_curve: 21, is_mandatory: 0, is_special_race: 0,
            external_ref: 'fixture-stakes', sort_order: 1,
        },
        {
            year: 2, month: 5, half: 'Early', turn: 9, slot_label: 'Early May',
            title: 'Fixture Sprint', tier: 'OP', grade_code: 400, distance: 1400,
            distance_band: 'Sprint', surface: 'Dirt', track_id: 10013, race_id: 101301,
            fans_needed: null, fans_gain_curve: null, is_mandatory: 0, is_special_race: 0,
            external_ref: 'fixture-sprint', sort_order: 2,
        },
    ];
    const seeded: SeededRaceSlot[] = [];

    try {
        for (const row of rows) {
            const result = db
                .prepare(
                    `INSERT INTO race_catalog_slots
                        (scenario_key, year, month, half, turn, slot_label, title, tier, grade_code,
                         distance, distance_band, surface, track_id, race_id, fans_needed,
                         fans_gain_curve, is_mandatory, is_special_race, did_not_exist, external_ref,
                         sort_order, source_url, snapshot_path, fetched_at, source_timezone, is_manual,
                         created_at, updated_at)
                     VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?,
                             ?, NULL, NULL, NULL, NULL, 0, ?, ?)`,
                )
                .run(
                    row.year, row.month, row.half, row.turn, row.slot_label, row.title, row.tier,
                    row.grade_code, row.distance, row.distance_band, row.surface, row.track_id,
                    row.race_id, row.fans_needed, row.fans_gain_curve, row.is_mandatory,
                    row.is_special_race, row.external_ref, row.sort_order, now, now,
                );

            seeded.push({ id: Number(result.lastInsertRowid), title: row.title });
        }

        return seeded;
    } finally {
        db.close();
    }
}

/** Removes exactly the rows this helper inserted, so the offline cases still see an empty calendar. */
export function removeRaceSlots(seeded: SeededRaceSlot[]): void {
    if (seeded.length === 0) {
        return;
    }

    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const ids = seeded.map((row) => row.id);
        db.prepare(`DELETE FROM race_catalog_slots WHERE id IN (${ids.map(() => '?').join(', ')})`).run(...ids);
    } finally {
        db.close();
    }
}

/**
 * The three enriched column sets the detail spec needs, written onto the catalogue's own seeded
 * rows by `external_ref` rather than invented as extra rows.
 *
 * The calendar is seeded; enrichment is not (it arrives through `uma:enrich-races`, which is not
 * part of `migrate --seed`). This helper reproduces the published shape for one populated row, one
 * partial row and one unenriched special race, and `clearRaceEnrichment()` puts the columns back so
 * a neighbouring case still sees the unenriched state.
 */
export interface EnrichedRaceIds {
    populated: number;
    partial: number;
    empty: number;
}

export function seedRaceEnrichment(): EnrichedRaceIds {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        const idFor = (externalRef: string): number => {
            const row = db
                .prepare('SELECT id FROM race_catalog_slots WHERE external_ref = ? LIMIT 1')
                .get(externalRef) as { id?: number } | undefined;

            if (row?.id === undefined) {
                throw new Error(`race fixture ${externalRef} is not in the scratch catalogue`);
            }

            return Number(row.id);
        };

        const populated = idFor('102201');
        const partial = idFor('406801');
        const empty = idFor('debut');

        db.prepare(
            `UPDATE race_catalog_slots
                SET track_name = ?, entries = ?, direction = ?, direction_label = ?,
                    course = ?, course_label = ?, season = ?, season_label = ?,
                    factor_effect_1 = ?, factor_effect_2 = ?, reward_summary = ?
              WHERE id = ?`,
        ).run(
            'Hanshin', 18, 1, 'Right-Handed', 3, null, 4, 'Winter (early)', 'speed', 'guts',
            JSON.stringify([
                { item: 4, num_min: 1, num_max: 1, chance: '1.000' },
                { item: 43, num_min: 5, num_max: 5, chance: '0.050' },
            ]),
            populated,
        );

        db.prepare(
            `UPDATE race_catalog_slots
                SET track_name = ?, entries = ?, direction = ?, direction_label = ?,
                    course = ?, course_label = ?, season = ?, season_label = ?,
                    factor_effect_1 = NULL, factor_effect_2 = NULL, reward_summary = NULL
              WHERE id = ?`,
        ).run('Chukyo', 16, 2, 'Left-Handed', 1, 'Inner', 3, 'Early Autumn', partial);

        return { populated, partial, empty };
    } finally {
        db.close();
    }
}

/** Restores the three rows to the unenriched state this suite's other race cases expect. */
export function clearRaceEnrichment(ids: EnrichedRaceIds): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare(
            `UPDATE race_catalog_slots
                SET track_name = NULL, entries = NULL, direction = NULL, direction_label = NULL,
                    course = NULL, course_label = NULL, season = NULL, season_label = NULL,
                    factor_effect_1 = NULL, factor_effect_2 = NULL, reward_summary = NULL
              WHERE id IN (?, ?)`,
        ).run(ids.populated, ids.partial);
    } finally {
        db.close();
    }
}
