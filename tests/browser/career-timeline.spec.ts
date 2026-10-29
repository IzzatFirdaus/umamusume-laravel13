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

test('prints a blank Energy or SP as an em dash, never a zero', async ({ page }) => {
    await openTimeline(page);

    const firstRow = page.locator('main table tbody tr').first();
    await expect(firstRow).toContainText('—');
    await expect(page.getByText(/a blank is "not recorded", never zero/)).toBeVisible();
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
