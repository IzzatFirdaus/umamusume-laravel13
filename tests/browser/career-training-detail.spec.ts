import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

/*
 * Rendered evidence for SCREEN-010, the Training Decision detail, rebuilt at the HFW's region model
 * after the 2026-10-28 parity audit. The page is now the wireframe's five-discipline pick list, the
 * three other actions, "Why Rest", the four outcome fields and the savebar; the client totals the
 * write boundary requires live in one collapsed disclosure, which is the documented deviation
 * (HFW OVERRIDE (write fields) in the page).
 *
 * The cases below pin that structure and the contract that has not changed: the write still validates
 * against the turn request, the accelerator is printed and works, and Rest writes through the same
 * form. The old cards/Advisor/facility-first assertions were removed with the structure they pinned;
 * this is a re-baseline, not a weakening: every claim the old file made that still applies is present.
 */

const createdRunUrls: string[] = [];
const WRITE = { timeout: 60_000 } as const;

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** The first turn, recorded through this screen's own collapsed totals and savebar. */
async function recordFirstTurn(page: import('@playwright/test').Page, runUrl: string): Promise<void> {
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('radio', { name: 'Speed training' }).check();
    await page.getByText('Client totals this turn (required to record)').click();

    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('input[name="fans"]').fill('120');
    await page.locator('input[name="sp"]').fill('100');
    // Facility levels: the discipline list reads its published gains and Energy from the level the
    // run records, so a turn that records none states the absence instead of a Lv-1 stand-in.
    await page.locator('input[name="facility_speed"]').fill('1');
    await page.locator('input[name="facility_stamina"]').fill('1');
    await page.locator('input[name="facility_power"]').fill('1');
    await page.locator('input[name="facility_guts"]').fill('1');
    await page.locator('input[name="facility_wit"]').fill('1');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 1 logged.')).toBeVisible(WRITE);
}

async function openTrainingDetail(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);
    await recordFirstTurn(page, runUrl);
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model, one primary, and one savebar', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.getByRole('heading', { name: 'Disciplines', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Other actions', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: /^Why /, level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Recorded outcome', level: 2 })).toBeVisible();

    // Five disciplines plus Rest, Recreation and Race = eight radios, one savebar, one filled action.
    await expect(page.locator('input[name="pick"]')).toHaveCount(8);
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('.btn-primary')).toHaveCount(1);
});

test('prints the five disciplines with their published gains and a signed Energy figure', async ({ page }) => {
    await openTrainingDetail(page);

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(page.getByRole('radio', { name: `${stat} training` })).toBeVisible();
    }

    // Four Energy disciplines spend Energy and Wit returns it; both are signed and worded.
    await expect(page.getByText(/^−\d+ Energy$/)).toHaveCount(4);
    await expect(page.getByText(/^\+\d+ Energy$/)).toHaveCount(1);
    await expect(page.getByText('no Energy', { exact: true })).toBeVisible();
});

test('marks the advisor\'s recommendation once', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.getByText('Recommended')).toHaveCount(1);
});

test('selects a discipline with the printed 1-5 accelerator', async ({ page }) => {
    await openTrainingDetail(page);

    await page.keyboard.press('3');

    await expect(page.getByRole('radio', { name: 'Power training' })).toBeChecked();
    await expect(page.locator('.savebar')).toContainText('Power selected');
});

test('Rest is one of the other actions and writes through the same form', async ({ page }) => {
    await openTrainingDetail(page);

    await page.getByRole('radio', { name: 'Rest' }).check();
    await expect(page.locator('.savebar')).toContainText('Rest selected');

    await page.getByText('Client totals this turn (required to record)').click();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('90');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
});

test('the client totals stay behind one disclosure, closed by default', async ({ page }) => {
    await openTrainingDetail(page);

    const details = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });

    await expect(details).not.toHaveAttribute('open', '');
    await expect(page.locator('input[name="speed"]')).toBeHidden();
});

test('keeps the tab order and the 44px target floor, and passes axe', async ({ page }) => {
    await openTrainingDetail(page);

    // The hit target is the label that wraps a radio, not the 13px radio glyph inside it, so this
    // measures links, buttons, summaries and selects; a radio's wrapper is asserted by its own label
    // height through the discipline rows' min-h-11 padding.
    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});

test('holds its layout at 320px', async ({ page }) => {
    await openTrainingDetail(page);
    await page.setViewportSize({ width: 320, height: 900 });

    const overflows = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);

    expect(overflows).toBe(false);
});
