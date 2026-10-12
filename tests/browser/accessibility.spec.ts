import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';

test.describe('Accessibility', () => {
    test('landing page passes WCAG axe scan', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });

    test('training runs list passes WCAG axe scan', async ({ page }) => {
        await page.goto('/training-runs');
        await page.locator('#app > *').first().waitFor();

        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });

    /*
     * KI-63: Inertia's bundled NProgress draws `role="bar"`, an invalid ARIA role, so a page-wide
     * axe scan fails at WCAG 2.2 A 4.1.2 whenever a visit is in flight — and the two scans above
     * pass only because a settled `goto` usually leaves none. The replacement indicator is a real
     * `progressbar` with an accessible name and the value attributes the role requires, so this
     * case holds a visit open and reads the live element: the semantics it renders, not the source
     * that promises them.
     */
    test('the visit progress indicator carries valid progressbar semantics mid-visit (KI-63)', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        await page.route('**/support-cards', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 2500));
            await route.continue();
        });

        await page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Support Cards' }).click();

        // The indicator is the indeterminate pattern the slice names: a native `progress` with an
        // accessible name and no value attribute. A real percentage is not available to invent -
        // Inertia reports one only for FormData uploads, and this app uploads none - so no
        // aria-valuenow and no trickle is offered either.
        const bar = page.getByRole('progressbar', { name: 'Page load progress' });
        await expect(bar).toBeVisible();

        // The whole point of the replacement: the page-wide scan that KI-63 could never pass
        // mid-visit is clean, and the invalid role is not in the document at all.
        const results = await buildAxe(page).analyze();
        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
        await expect(page.locator('[role="bar"]')).toHaveCount(0);

        await page.waitForURL(/\/support-cards$/);

        // Settled: the indicator leaves the tree entirely, so the settled scans above scan a
        // document with no progress control in it.
        await expect(page.getByRole('progressbar')).toHaveCount(0);
    });
});
