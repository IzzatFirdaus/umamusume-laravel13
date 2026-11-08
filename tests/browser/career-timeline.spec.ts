import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered evidence for SCREEN-018, the Career Timeline, re-baselined at the HFW's region model after
 * the 2026-10-28 parity audit: the four-count strip, the Kind/Year filters, one record table with the
 * Turn spine and the blank Energy/SP cells, the pager, and the collapsible phase grouping.
 *
 * Fixture: a Trackblazer career with two logged turns, deleted over HTTP in afterEach.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    return runUrl;
}

async function openTimeline(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await createRun(page);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, 2);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

/**
 * `R6-12`: a run whose two turns record distinct Energy bands and distinct SP totals, so a rendered
 * row cannot borrow its neighbour's reading and a latest-turn substitution cannot pass.
 */
async function openTimelineWithReadings(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await createRun(page);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 1, { energy_state: 'band', energy_band: 'low', sp: '173' });
    await recordTurns(page, runId, 1, { turn: '2', energy_state: 'band', energy_band: 'mid', sp: '0' });

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

/** The Energy and SP cell texts of one record row, by turn. */
async function readingCells(page: import('@playwright/test').Page, turn: number): Promise<{ energy: string; sp: string }> {
    const row = page.locator('main table tbody tr').filter({ has: page.locator('td:first-child', { hasText: new RegExp(`^${turn}$`) }) }).first();

    return {
        energy: ((await row.locator('td:nth-child(5)').textContent()) ?? '').trim(),
        sp: ((await row.locator('td:nth-child(6)').textContent()) ?? '').trim(),
    };
}

test('prints each row its own Energy band and its own SP, and a correction moves only its row (R6-12)', async ({ page }) => {
    // The page read `entry.after?.energy` / `entry.after?.sp`, keys the server never sent, so both
    // cells printed N/A on every row. Two turns with distinct bands and SP totals: a latest-turn
    // substitution, a constant, or one-facet repair cannot pass.
    const runUrl = await openTimelineWithReadings(page);

    const first = await readingCells(page, 1);
    const second = await readingCells(page, 2);

    expect(first.energy, 'turn 1 prints the band it recorded').toBe('Low band');
    expect(first.sp, 'turn 1 prints the SP it recorded').toBe('173');
    expect(second.energy, 'turn 2 prints its own band, not turn 1\u2019s').toBe('Mid band');
    expect(second.sp, 'turn 2 prints its own SP, not turn 1\u2019s').toBe('0');

    // A correction to turn 1 changes turn 1's rendered reading and leaves turn 2 untouched. This is
    // the half a "read the latest turn for every row" fix would fail.
    await page.getByRole('link', { name: 'Edit turn 1' }).first().click();
    await page.waitForURL(/\/training-runs\/\d+\/turns\/\d+\/edit$/, WRITE);
    await page.selectOption('#energy_state', { label: 'band' });
    await page.locator('#energy_band').fill('high');
    await page.getByRole('button', { name: 'Save correction' }).click();
    await expect(page.getByText('Saved.')).toBeVisible();

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const corrected = await readingCells(page, 1);
    const untouched = await readingCells(page, 2);

    expect(corrected.energy, 'the corrected row prints its new band').toBe('High band');
    expect(untouched.energy, 'the neighbour row is unchanged').toBe('Mid band');
    expect(untouched.sp, 'the neighbour row is unchanged').toBe('0');
});

test('renders the empty state when no turns have been logged', async ({ page }) => {
    const runUrl = await createRun(page);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Every logged turn, race and event', level: 1 })).toBeVisible();
    await expect(page.getByText(/No turns have been recorded/)).toBeVisible();
});

test('renders the HFW region model: four counts, one record table and the pager', async ({ page }) => {
    await openTimeline(page);

    for (const label of ['Turns recorded', 'Races entered', 'Events', 'Finale']) {
        await expect(page.locator('main').getByText(label, { exact: true }).first()).toBeVisible();
    }

    await expect(page.getByRole('heading', { name: 'The record', level: 2 })).toBeVisible();
    await expect(page.locator('main table')).toHaveCount(1);

    const headers = await page.locator('main table thead th').allTextContents();
    expect(headers.map((header) => header.trim())).toEqual(['Turn', 'Position', 'Kind', 'What happened', 'Energy', 'SP']);

    await expect(page.getByText(/entries · showing/)).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Phase grouping', level: 2 })).toBeVisible();
});

test('filters by kind and clears back to every entry', async ({ page }) => {
    await openTimeline(page);

    const before = await page.locator('main table tbody tr').count();
    expect(before).toBeGreaterThan(0);

    await page.selectOption('select[name="kind"]', 'Turn');
    await expect(page.locator('main table tbody tr').first()).toContainText('Turn');

    await page.getByRole('button', { name: 'Clear', exact: true }).click();
    await expect(page.locator('main table tbody tr')).toHaveCount(before);
});

test('groups the log by phase and keeps the finale block outside the grid', async ({ page }) => {
    await openTimeline(page);

    for (const phase of ['Junior Year', 'Classic Year', 'Senior Year', 'After the grid']) {
        await expect(page.locator('summary', { hasText: phase })).toBeVisible();
    }
});

test('prints an unrecorded Energy or SP as a labelled N/A, never a dash and never a zero', async ({ page }) => {
    // Re-baselined 2026-10-29: this case pinned the em dash as the absence glyph, which `AGENTS.md` §5
    // and `RenderedCopyHygieneTest` ban ("never a default and never a dash"). The intent it guards -
    // an unrecorded figure stays unrecorded rather than reading as 0 - is unchanged, so the
    // assertions now sweep every Energy and SP cell: none may print a glyph, and every `N/A` must
    // carry its reason. Sweeping the column rather than row 1 keeps the case honest whatever the
    // seeded run happened to record.
    await openTimeline(page);

    const cells = page.locator('main table tbody tr td:nth-child(5), main table tbody tr td:nth-child(6)');
    expect(await cells.count(), 'the log renders its Energy and SP columns').toBeGreaterThan(0);

    const shapes = await cells.evaluateAll((nodes) =>
        nodes.map((node) => ({
            text: (node.textContent ?? '').trim(),
            title: node.getAttribute('title'),
        })),
    );

    for (const cell of shapes) {
        expect(cell.text, 'no absence glyph in a figure cell').not.toBe('—');
        expect(cell.text, 'no absence glyph in a figure cell').not.toBe('–');

        if (cell.text === 'N/A') {
            expect(cell.title, 'an N/A names why the figure is absent').toBeTruthy();
        }
    }

    await expect(page.getByText(/an N\/A is "not recorded", never zero/)).toBeVisible();
});

test('sizes the controls to the 44px contract and reflows at 320px', async ({ page }) => {
    await openTimeline(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('heading', { name: 'The record', level: 2 })).toBeVisible();
});

test('renders no write form, so the timeline stays read-only', async ({ page }) => {
    await openTimeline(page);

    await expect(page.locator('form')).toHaveCount(0);
    await expect(page.locator('button[type="submit"]')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Every logged turn, race and event', level: 1 })).toBeVisible();
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openTimeline(page);

    const results = await buildAxe(page).analyze();

    expect(results.violations).toEqual([]);
});

test('labels the undo with the last logged turn, not the rail maximum (R7-27)', async ({ page }) => {
    // The label is computed in the page from the rail's TURN rows (`latestLoggedTurn`), so a payload
    // assertion cannot reach the rendered string -- the sibling Feature case pins the number the
    // component feeds it, this one pins what a Trainer reads. Both are needed: the first cannot catch a
    // wrong binding, the second cannot catch a wrong number.
    const runId = await openTimelineWithReadings(page);
    expect(runId).not.toBe('');

    const undo = page.getByRole('button', { name: /Undo turn \d+/ });
    await expect(undo).toBeVisible();

    // `openTimelineWithReadings` logs turns 1 and 2, so the undo names the second: the turn it would
    // remove, which is the highest turn IN THE LOG. Turn 0 would name a turn no career has, and turn 3
    // would name one the rail does not hold -- the defect class the `Math.max(0, ...)` derivation had.
    await expect(undo).toHaveAccessibleName('Undo turn 2');

    // And the control is inert, matching what its own title says (R7-21's rule, same shell).
    await expect(undo).toBeDisabled();
});
