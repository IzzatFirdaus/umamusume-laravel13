import { test, expect } from './support/base-test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

/*
 * Error summary (plan 5.1, GOV.UK "Recover from validation errors"). A failed submit gets two
 * halves: the per-field message FormField already owns through `aria-describedby`, and this summary
 * at the top of the form. These cases assert what only a browser reaches: after a refused press
 * focus lands on the summary, the summary lists one entry per invalid field, each entry's anchor
 * resolves to that field's own id, and a clean form carries no summary landmark at all.
 *
 * The refusal is produced by the app itself. A duplicate turn number passes every client-side
 * constraint (`StoreTurnEntryRequest::rules()` bounds each field the form renders, and the inputs
 * carry the same `min`/`max`), but the request's unique rule refuses it, so it comes back as a field
 * error rather than being seeded into the database.
 */
const createdRunUrls: string[] = [];

const WRITE = { timeout: 60_000 } as const;

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** Fill the disclosure with one complete, valid turn; the caller breaks exactly one value. */
async function fillValidTurn(page: import('@playwright/test').Page): Promise<void> {
    const totals = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });
    if (!(await totals.evaluate((el) => (el as HTMLDetailsElement).open))) {
        await totals.locator('summary').click();
    }

    await page.getByRole('radio', { name: 'Speed training' }).check();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('input[name="fans"]').fill('120');
    await page.locator('input[name="sp"]').fill('100');
    await page.locator('select[name="outcome"]').selectOption('Success');
}

/** Log turn 1 through the app, then land back on the screen for the next turn. */
async function openNextTurn(page: import('@playwright/test').Page, runUrl: string): Promise<void> {
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await fillValidTurn(page);
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    // Turn 2 is now the next thing to record, and its disclosure opens by default (round 5 S7).
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('a refused turn puts focus on the summary, one entry per invalid field, anchored to the field', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);
    await openNextTurn(page, runUrl);

    // Fill a complete turn, then name a turn the run already logged: turn 1 is taken, so the request
    // refuses it while every field still satisfies its own client-side constraint.
    await fillValidTurn(page);
    await page.locator('#turn-turn').fill('1');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();

    const summary = page.locator('[data-region="error-summary"]');
    await expect(summary).toBeVisible(WRITE);
    await expect(summary).toBeFocused();
    await expect(summary.getByRole('listitem')).toHaveCount(1);

    // The entry names the field and links to that field's own id, so the anchor resolves to the
    // control the Trainer has to fix rather than to a dead fragment.
    const link = summary.getByRole('link');
    await expect(link).toContainText('Turn');
    await expect(link).toHaveAttribute('href', '#turn-turn');
    await expect(page.locator('#turn-turn')).toBeVisible();

    // Pressing the entry moves focus to the field it names.
    await link.click();
    await expect(page.locator('#turn-turn')).toBeFocused();
});

test('a clean training screen carries no summary landmark', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);
    await openNextTurn(page, runUrl);

    await expect(page.locator('[data-region="error-summary"]')).toHaveCount(0);
});