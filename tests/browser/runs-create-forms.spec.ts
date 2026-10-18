import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * The FormField family's first pilot (plan 1.8b), on `Runs/Create.vue`.
 *
 * Two things are asserted, and they are different kinds of claim:
 *
 *   1. STRUCTURE — the page renders the family's control, and the required field no longer carries an
 *      asterisk while the optional ones still say "(optional)". Read from the DOM, never a capture.
 *   2. BEHAVIOUR — a real submit still writes a run. A primitive that renders correctly and breaks the
 *      POST would pass every structural assertion, so the round trip is the one that matters.
 *
 * The run this case creates is deleted afterwards so the browser suite leaves no row behind.
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test('the create form uses the FormField family and marks only the optional fields', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The family's Select renders the same underlying control; the page's own id is preserved so the
    // request still posts the same field.
    const scenario = page.locator('select#create-scenario');
    await expect(scenario).toBeVisible();
    await expect(scenario).toHaveAttribute('name', 'scenario');

    // Optional stays named as optional.
    await expect(page.locator('label[for="create-scenario"]')).toContainText('(optional)');

    // Required is the default and is UNMARKED: the asterisk this page used to print is gone, and the
    // field is not called "(optional)" either.
    const statusLabel = page.locator('label[for="create-status"]');
    await expect(statusLabel).toHaveText('Status');
    await expect(statusLabel).not.toContainText('*');
    await expect(statusLabel).not.toContainText('optional');

    // The required control still says so to assistive technology, which is what the asterisk was for.
    await expect(page.locator('select#create-status')).toHaveAttribute('aria-required', 'true');
});

test('a submit through the family still writes the run', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Agnes Digital is in the seeded roster; the combobox commits on Enter.
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');

    await page.locator('select#create-scenario').selectOption('unity_cup');
    await page.locator('select#create-status').selectOption('Active');

    await page.getByRole('button', { name: /Create run/ }).click();

    // The POST succeeded: the browser moved off the create page onto the new run's own record.
    await page.waitForURL(/\/training-runs\/\d+/, { timeout: 20000 });
    createdRunUrls.push(new URL(page.url()).pathname);

    await expect(page.locator('main')).toContainText('Agnes Digital');
});
