import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * F1, the career surface cutover: no normal navigation path may open the 0.1.0 run-detail page
 * (`SCR-RUN-003`), and Dashboard and Careers must converge on the Cockpit (`SCR-CAR-011`).
 *
 * What is asserted here is the state this slice ships: the Careers rows and the Dashboard's Resume
 * Career both carry the Cockpit's URL. The legacy-URL redirect itself (opening /training-runs/{id}
 * directly) is deliberately not asserted yet. The owner held it, and the reason is not a test count:
 * four writes have no 2.0 owner yet, so the record screen is still the only place a Trainer can reach
 * them (plan §9's F1 row). This file's header comment is where that case belongs when it lands.
 *
 * Fixture strategy: the run is created through the create screen the way the other career specs do
 * it, and deleted over HTTP in afterEach through the shared `tests/utils/delete-run.ts`, so the
 * cleanup no longer depends on the run record screen rendering.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createUnityCupRun(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());
}

/** The Cockpit for an existing run, reached from the Careers list the way a Trainer arrives. */
async function openCareersAndSelectRun(page: import('@playwright/test').Page): Promise<void> {
    await createUnityCupRun(page);
    await page.goto('/training-runs', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: /Rice Shower/ }).first().click();
    await page.waitForURL(/\/cockpit$/, WRITE);
    await page.locator('#app > *').first().waitFor();
}

test('Dashboard Resume Career opens the Cockpit, not the run-detail page', async ({ page }) => {
    await createUnityCupRun(page);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Resume Career' }).click();
    await page.waitForURL(/\/cockpit$/, WRITE);
    await page.locator('#app > *').first().waitFor();

    // The Cockpit is the destination: its own regions render, and the legacy page's identity
    // ("Run: {name}") does not appear as the document title.
    await expect(page.getByRole('heading', { name: 'Stats and state' })).toBeVisible();
    expect(await page.title()).not.toMatch(/^Run: /);
    expect(new URL(page.url()).pathname).toMatch(/\/cockpit$/);
});

test('selecting a career from Careers opens the Cockpit directly', async ({ page }) => {
    await openCareersAndSelectRun(page);

    await expect(page.getByRole('heading', { name: 'Stats and state' })).toBeVisible();
    expect(await page.title()).not.toMatch(/^Run: /);
    expect(new URL(page.url()).pathname).toMatch(/\/cockpit$/);
});

test('Dashboard Resume and Careers selection converge on the same Cockpit surface', async ({ page }) => {
    await createUnityCupRun(page);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Resume Career' }).click();
    await page.waitForURL(/\/cockpit$/, WRITE);
    const fromDashboard = new URL(page.url()).pathname;

    await page.goto('/training-runs', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: /Rice Shower/ }).first().click();
    await page.waitForURL(/\/cockpit$/, WRITE);
    const fromCareers = new URL(page.url()).pathname;

    expect(fromCareers, 'the two paths land on different career surfaces').toBe(fromDashboard);
});

test('the Cockpit keeps its own 2.0 decision links after the cutover', async ({ page }) => {
    await openCareersAndSelectRun(page);

    // Only the links that exist today (SCREEN-009's action grid). The cutover must not have
    // broken the 2.0 navigation that was already there.
    await expect(page.getByRole('link', { name: /^Training/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /^Race/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /^Event/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /^Inheritance/ })).toBeVisible();
});

test('the Cockpit carries the door to the Career Result', async ({ page }) => {
    await openCareersAndSelectRun(page);

    // D14 ruled a screen reachable only by URL a defect. The Career Result's one door was the 0.1.0
    // record page's header, so it needed a 2.0 door of its own: the left column, beside the timeline.
    const door = page.getByRole('link', { name: 'Career Result' });
    await expect(door).toBeVisible();

    const box = await door.boundingBox();
    expect(box?.height ?? 0, 'the Career Result door is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    await door.click();
    await page.waitForURL(/\/result$/, WRITE);
    await page.locator('#app > *').first().waitFor();
    expect(await page.title()).toMatch(/^Career result: /);
});

test('the whole selection flow works from the keyboard and reflows at 320 px', async ({ page }) => {
    await createUnityCupRun(page);

    // Keyboard: focus the Careers destination in the sidebar, open it, then step to the run row
    // and open it. No pointer interaction anywhere.
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Careers' }).first().focus();
    await expect(page.getByRole('link', { name: 'Careers' }).first()).toBeFocused();
    await page.keyboard.press('Enter');
    await page.waitForURL(/\/training-runs$/, WRITE);
    await page.locator('#app > *').first().waitFor();

    const row = page.getByRole('link', { name: /Rice Shower/ }).first();
    row.focus();
    await expect(row).toBeFocused();
    await page.keyboard.press('Enter');
    await page.waitForURL(/\/cockpit$/, WRITE);

    // Reflow: no sideways scroll at the narrow viewport with the Cockpit open.
    await page.setViewportSize({ width: 320, height: 900 });
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});
