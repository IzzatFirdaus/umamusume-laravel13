import { test, expect } from './support/base-test';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { seedPendingCandidates, clearPendingCandidates } from '../utils/review-candidates';

/*
 * Rendered follow-up to the 2026-11-18 comprehensive walk. Each case re-measures a static
 * doc-vs-code candidate in the browser before it is filed, and pins the fix that followed:
 *  - the turn log's clipped columns must be reachable by keyboard (DESIGN.md 458-466);
 *  - the Transfer Requests title must sit at the page-title tier (DESIGN.md 368);
 *  - one provenance word per review fact (DESIGN.md 974-980, A-96);
 *  - an absent Dashboard tile must state its reason in prose, not only a title (DESIGN.md 893-899).
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
    clearPendingCandidates();
});

async function createRun(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { timeout: 60_000 });
    createdRunUrls.push(page.url());
    return page.url().replace(/\/cockpit$/, '');
}

test('turn log is a labelled keyboard-scrollable region at the reflow width', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    const runUrl = await createRun(page);
    await recordTurns(page, runUrl.split('/').pop() ?? '', 2);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const region = page.getByRole('region', { name: 'Turn log, scrollable' });
    await expect(region).toHaveCount(1);
    await expect(region).toHaveAttribute('tabindex', '0');

    await region.focus();
    const focused = await region.evaluate((el) => el === document.activeElement);
    expect(focused, 'the scroll region itself must take keyboard focus').toBe(true);

    const before = await region.evaluate((el) => el.scrollLeft);
    await page.keyboard.press('ArrowRight');
    await page.waitForTimeout(200);
    const after = await region.evaluate((el) => el.scrollLeft);
    expect(after, 'arrow keys must move the clipped columns into view').toBeGreaterThan(before);
});

test('Transfer Requests title renders at the page-title tier', async ({ page }) => {
    await page.goto('/veterans/transfers', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const size = await page.locator('h1').evaluate((el) => getComputedStyle(el).fontSize);
    expect(size, 'the page title must match the 30px Critical tier used by its siblings').toBe('30px');
});

test('a no-suggestion review row carries one provenance word, not two', async ({ page }) => {
    clearPendingCandidates();
    seedPendingCandidates(2);
    await page.goto('/review', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const row = page.locator('#review-results tr').filter({ hasText: 'Unknown' }).first();
    await expect(row).toContainText('Unknown');
    expect(await row.getByText('No match', { exact: true }).count(), 'the duplicate pill must be gone').toBe(0);
});

test('an absent Dashboard tile states its reason in visible prose', async ({ page }) => {
    await createRun(page);
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.locator('[data-tile="energy"]')).toContainText('not recorded');
    await expect(page.getByText('No logged turn has recorded Energy for this career.')).toBeVisible();
});
