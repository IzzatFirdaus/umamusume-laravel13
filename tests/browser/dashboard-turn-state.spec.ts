import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { startCareer } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';
import { deleteRun } from '../utils/delete-run';
import { resolvedScratchDatabase } from './global-setup';

/*
 * RTX-02 — the Dashboard must not state a turn position for a career that has none.
 *
 * Authority: `AUDIT-AND-VERIFICATION.md` section `## 04-ux-findings-retest.md` (the folded source), lines 60-78 of the source folded 2026-11-12 (Round 26, owner-ordered).
 * Expected: "the resume card states a turn position the cockpit can also show."
 * Actual:   "the card prints `TURN 0 of 72` and `Turn 0 logged` for a run whose cockpit says
 *            `No turn recorded yet`; the cockpit deliberately shows no number ..., so the two
 *            surfaces use different vocabularies for the same empty state."
 * Recommendation: "have the card print the cockpit's phrase when no turn exists."
 *
 * Three cases, because the card has three states and only the first is the defect:
 *   1. zero turns        -> no number, the cockpit's own empty-state wording
 *   2. one logged turn   -> the real position ("Turn 1 logged"), not the empty state
 *   3. imported career   -> the logged count AND the imported position, both named
 *
 * `startCareer` creates a run with no turns, which is exactly case 1: the wizard never logs one.
 */

const createdRunUrls: string[] = [];

const DB = resolvedScratchDatabase();

const HARNESS_ENV = {
    ...process.env,
    DB_DATABASE: DB,
    UMA_DATABASE_ROLE: 'browser',
    UMA_EXPECTED_DATABASE: DB,
};

/**
 * The first run the imported-runs seeder produced, which is the only kind carrying a
 * `career_position`. `docs/imports/ura-finale-runs.md` records that the seeder sets
 * `career_position_source = 'imported'` for the runs whose header names a calendar position.
 */
function findImportedRun(): { id: number } {
    const out = execFileSync(
        'php',
        ['artisan', 'tinker', '--execute='
            + 'echo \\App\\Models\\TrainingRun::query()'
            + '->where("career_position_source", "imported")->value("id");'],
        { cwd: process.cwd(), env: HARNESS_ENV, encoding: 'utf8' },
    );

    const id = Number((out.match(/^\s*(\d+)\s*$/m) ?? [])[1]);

    expect(Number.isInteger(id), `no imported run in the scratch database: ${out}`).toBe(true);

    return { id };
}

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 300_000 });

/** The Dashboard's note paragraph, which is where the turn sentence lives. */
function noteParagraph(page: import('@playwright/test').Page) {
    return page.locator('p').filter({ hasText: /logged/i }).first();
}

test('a career with no logged turn states no position', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const note = noteParagraph(page);
    await expect(note).toBeVisible();

    // The defect: "Turn 0 logged" claims a position the run does not have.
    await expect(
        note,
        'the note states a logged turn for a career that has recorded none',
    ).not.toContainText(/Turn 0 logged/i);

    // The cockpit's own wording for the same empty state, which the recommendation asks the card to
    // adopt. `AGENTS.md` §13: an unrecorded value renders as an absence with a disclosure, never a
    // default and never a dash.
    await expect(
        note,
        'the note does not use the cockpit\'s empty-state wording for a run with no turns',
    ).toContainText(/no turn (has been|recorded)/i);

    // And the Turn tile must not print a position either.
    const turnTile = page.locator('[data-tile="turn"]').first();
    await expect(turnTile, 'the Turn tile prints a position for a career with no turns')
        .not.toContainText(/\b0\b/);
});

test('a logged turn reads as the position it is', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    const runId = /\/training-runs\/(\d+)/.exec(runUrl)?.[1];
    expect(runId, `not a training-run URL: ${runUrl}`).toBeDefined();
    await recordTurns(page, String(runId), 1);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The fix must not silence a state that is genuinely recorded.
    await expect(noteParagraph(page)).toContainText(/Turn 1 logged/i);
});

test('an imported career names both the logged count and the imported position', async ({ page }) => {
    // Case 3 uses the repository's own imported fixture rather than a hand-set column: the seeder
    // writes `career_position` + `career_position_source = 'imported'` per
    // `docs/imports/ura-finale-runs.md`, which is the state this clause exists for.
    execFileSync('php', ['artisan', 'db:seed', '--class=UraFinaleRunsSeeder', '--no-interaction'], {
        cwd: process.cwd(),
        env: HARNESS_ENV,
        encoding: 'utf8',
    });

    const { id: importedRunId } = findImportedRun();

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Resume the imported career so the card describes it rather than the wizard-created one.
    await page.goto(`/training-runs/${importedRunId}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const note = noteParagraph(page);

    // The pair is the contract: "how many turns are logged" AND "where the run was imported at".
    // An imported run carries both, so both clauses must be present and both must name their frame.
    await expect(note).toContainText(/Turn \d+ logged/i);
    await expect(
        note,
        'an imported career lost the position it was imported at',
    ).toContainText(/imported at turn \d+/i);
});
