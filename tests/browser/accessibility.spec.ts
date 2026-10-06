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
});
