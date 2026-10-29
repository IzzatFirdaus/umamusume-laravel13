import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered evidence for SCREEN-023, the Scenario Race Planner, re-baselined at the HFW's region model
 * after the 2026-10-28 parity audit: Deadlines, Planning from / Held figures / Readiness, the Mandatory
 * and Upcoming tables, and the Reward comparison card with its empty state.
 *
 * Fixture: eleven logged turns put the planner on Junior turn 12, where the calendar carries the one
 * mandatory in-year race. The run is deleted over HTTP in afterEach.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openPlanner(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(page.url());

    await recordTurns(page, runId, 11);

    await page.goto(`${runUrl}/races/planner`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model and its empty comparison state', async ({ page }) => {
    await openPlanner(page);

    for (const heading of ['Deadlines', 'Planning from', 'Held figures', 'Readiness', 'Mandatory', 'Upcoming', 'Reward comparison']) {
        await expect(page.getByRole('heading', { name: heading, level: 2 })).toBeVisible();
    }

    await expect(page.locator('main table')).toHaveCount(2);
    await expect(page.getByRole('heading', { name: 'Nothing to compare yet' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Plan the calendar', level: 1 })).toBeVisible();
});

test('names the held figures and states the next obligation without promising it', async ({ page }) => {
    await openPlanner(page);

    await expect(page.locator('#held-heading')).toBeVisible();
    await expect(page.getByText('Win probability:')).toBeVisible();
    await expect(page.getByText('Expected risk:')).toBeVisible();
    await expect(page.getByText(/This states the next obligation the run has not cleared/)).toBeVisible();
});

test('keeps a maiden-locked race visible with its reason and still comparable', async ({ page }) => {
    await openPlanner(page);

    const reasons = page.getByText('Maiden lock not cleared');

    if (await reasons.count() === 0) {
        test.skip(true, 'the fixture cleared the maiden lock');

        return;
    }

    await expect(reasons.first()).toBeVisible();
    // Planning is where a locked race is still weighed, so its Compare control stays enabled.
    await expect(page.locator('main table').first().getByRole('button', { name: 'Compare' }).first()).toBeEnabled();
});

test('compares two races side by side and clears back to the empty state', async ({ page }) => {
    await openPlanner(page);

    const compares = page.getByRole('button', { name: 'Compare' });

    expect(await compares.count()).toBeGreaterThanOrEqual(2);

    await compares.nth(0).click();
    await compares.nth(1).click();

    await expect(page.getByRole('heading', { name: 'Nothing to compare yet' })).toHaveCount(0);
    await expect(page.getByText('2 races selected')).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Property' })).toBeVisible();

    await page.getByRole('button', { name: 'Clear selection' }).click();

    await expect(page.getByRole('heading', { name: 'Nothing to compare yet' })).toBeVisible();
});

test('sizes the planner controls to the 44px contract', async ({ page }) => {
    await openPlanner(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);
});

test('reflows at 320px without pushing the page sideways', async ({ page }) => {
    await openPlanner(page);
    await page.setViewportSize({ width: 320, height: 900 });

    expect(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1)).toBe(false);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openPlanner(page);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});
