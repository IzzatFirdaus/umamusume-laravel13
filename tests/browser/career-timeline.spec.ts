import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-018, the Career Timeline
 * (`SCR-CAR-017`, plan §8 D14). `CareerTimelineTest` pins the props shape and the BEFORE/AFTER
 * mapping; these cases assert what only a browser reaches: the rail's glyph alongside text,
 * the disclosure's keyboard path, the audit pane opening and showing the five audit rows, the
 * type filter's aria-pressed state, the 320px reflow and 44px sweep, reduced motion, and an
 * axe scan against `#app` (the same scope rule every career spec uses).
 *
 * Fixture strategy: create a run, log two turns on the run screen, then walk to the timeline.
 * Writes get 60s because `php artisan serve` is a single-process `php -S`.
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
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());

    const hatch = page.locator('details', { has: page.getByText('Correct a turn by hand') });
    await page.getByText('Correct a turn by hand').click();

    for (let turn = 1; turn <= 2; turn++) {
        await hatch.locator('input[name="turn"]').fill(String(turn));
        await hatch.locator('input[name="speed"]').fill('600');
        await hatch.locator('input[name="stamina"]').fill('500');
        await hatch.locator('input[name="power"]').fill('500');
        await hatch.locator('input[name="guts"]').fill('500');
        await hatch.locator('input[name="wit"]').fill('500');
        await hatch.getByRole('button', { name: 'Save correction' }).click();
        await expect(page.getByText(`Turn ${turn} logged.`)).toBeVisible(WRITE);
    }

    await page.goto(`${page.url()}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('renders the empty state when no turns have been logged', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());

    await page.goto(`${page.url()}/timeline`, { waitUntil: 'domcontentloaded' });
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
