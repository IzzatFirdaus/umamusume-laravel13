import { test, expect } from './support/base-test';

/*
 * AUD-20261118-001 browser walk, 2026-11-18. The wizard focuses the heading of the step it
 * renders (SetupLayout.vue), but the persistent shell did not: clicking a sidebar link left focus
 * on `body`, so a keyboard or screen-reader Trainer got no signal that the page changed. The one
 * global rule in spa.ts now does for the shell what the wizard already did for its steps.
 *
 * These are rendered-focus assertions, so they belong in the browser suite; a source grep could
 * only prove the listener exists, not that focus moved.
 */
test('sidebar navigation moves focus to the destination heading', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    await page.locator('aside').getByRole('link', { name: 'Skills' }).first().click();
    await page.waitForURL(/\/skills$/);

    await expect(page.getByRole('heading', { level: 1, name: 'Skill search' })).toBeFocused();
});

test('a same-page filter does not steal focus into the heading', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-search').fill('speed');
    await page.locator('#skill-search').press('Enter');
    await expect(page).toHaveURL(/search=speed/);

    const headingTookFocus = await page.locator('h1').evaluate((el) => el === document.activeElement);
    expect(headingTookFocus, 'the filter re-rendered the same page and must keep the Trainer in the form').toBe(false);
});
