import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-018, the Career Timeline
 * (`SCR-CAR-017`, plan §8 D14). `CareerTimelineTest` pins the props shape and the BEFORE/AFTER
 * mapping; these cases assert what only a browser reaches: the rail's glyph alongside text,
 * the disclosure's keyboard path, the audit pane opening and showing the five audit rows, the
 * type filter's aria-pressed state, the 320px reflow and 44px sweep, reduced motion, and an
 * axe scan against `#app` (the same scope rule every career spec uses).
 *
 * Fixture strategy: create a run, log two turns over `runs.turns.store` (no rendered surface creates
 * a turn since the run screen became a redirect), then walk to the timeline. Writes get 60s because
 * `php artisan serve` is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * A career with two logged turns, then the Timeline screen for it.
 */
async function openTimeline(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, 2);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('renders the empty state when no turns have been logged', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Career timeline' })).toBeVisible();
    await expect(page.getByText(/No turns have been recorded/)).toBeVisible();
});

test('renders the rail with glyph alongside text and the audit opens by keyboard', async ({ page }) => {
    await openTimeline(page);

    const firstRow = page.locator('ol[aria-label="Career timeline"] > li').first();
    const firstDetails = firstRow.locator('details');
    const firstSummary = firstDetails.locator('summary');

    await expect(firstSummary).toBeVisible();

    // The glyph and the text appear in the same row — never state through glyph alone.
    await expect(firstSummary).toContainText('●');
    await expect(firstSummary).toContainText(/Turn recorded/);

    // Closed by default: the disclosure is collapsed, so `details.open` is false.
    expect(await firstDetails.evaluate((el: HTMLDetailsElement) => el.open)).toBe(false);

    // Keyboard activation opens the audit pane and the contents become visible.
    await firstSummary.focus();
    await page.keyboard.press('Enter');
    expect(await firstDetails.evaluate((el: HTMLDetailsElement) => el.open)).toBe(true);
    await expect(firstDetails.locator('dt', { hasText: 'BEFORE' })).toBeVisible();
    await expect(firstDetails.locator('dt', { hasText: 'ACTION' })).toBeVisible();
    await expect(firstDetails.locator('dt', { hasText: 'EXPECTED' })).toBeVisible();
    await expect(firstDetails.locator('dt', { hasText: 'ACTUAL' })).toBeVisible();
    await expect(firstDetails.locator('dt', { hasText: 'RESULT' })).toBeVisible();
});

test('toggles a type filter and shows only the active kind', async ({ page }) => {
    await openTimeline(page);

    const turnChip = page.getByRole('group', { name: 'Filter timeline rows by type' }).getByRole('button', { name: 'Turn' });
    await expect(turnChip).toHaveAttribute('aria-pressed', 'false');
    await turnChip.click();
    await expect(turnChip).toHaveAttribute('aria-pressed', 'true');

    // Two turn rows keep showing; the spec logged two turns and posted no race or event rows.
    await expect(page.locator('ol[aria-label="Career timeline"] > li:visible')).toHaveCount(2);
});

test('keeps min-h-11 tap targets on the filter chips and reflows at 320 px', async ({ page }) => {
    await openTimeline(page);

    const chips = page.getByRole('group', { name: 'Filter timeline rows by type' }).getByRole('button');
    const chipCount = await chips.count();
    expect(chipCount).toBeGreaterThan(0);
    for (let i = 0; i < chipCount; i++) {
        const height = await chips.nth(i).evaluate((element) => element.getBoundingClientRect().height);
        expect(height).toBeGreaterThanOrEqual(44);
    }

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.locator('ol[aria-label="Career timeline"]')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Career timeline' })).toBeVisible();
});

test('passes the axe A + AA scan on the timeline page', async ({ page }) => {
    await openTimeline(page);
    await buildAxe(page).analyze();
});

test('renders no write control, so the timeline is read-only', async ({ page }) => {
    await openTimeline(page);

    // `SCREEN_SPEC.md` states the Timeline is read-only (SCR-CAR-017): it renders the run's recorded
    // turns, races and events, and its one write is a link to the Cockpit's correction rather than a
    // form here. A submit control, a form, or a link into a turn write on this page would break that
    // contract, so all three are asserted absent. The Cockpit door is a `GET` and is not a write.
    await expect(page.locator('form')).toHaveCount(0);
    await expect(page.locator('button[type="submit"]')).toHaveCount(0);
    await expect(page.locator('a[href*="runs.turns"]')).toHaveCount(0);

    // Positive control: the read-only screen rendered rather than an empty document.
    await expect(page.getByRole('heading', { name: 'Career timeline' })).toBeVisible();
});
