import { test, expect } from '@playwright/test';
import { resetLayout, setLayout } from './support/layout';

/*
 * Cross-layout evidence for the Layout width preference (DL.14 / PLAN 6B.15). The feature test
 * `PreferenceControlsTest` pins the shared prop; these assert the class is actually applied on every
 * shell at 1920 x 1080.
 *
 * Since all layouts render through AppLayout's `<main>`, `#main` is the stable container across every
 * shell, and it carries the shared `layoutWidthClass` from `useLayout()`.
 *
 * The save control is named `Save changes` (the older `Save preferences` name went with the v3 page),
 * and the page's own status line reads `All changes saved`; `support/layout.ts` owns both.
 */

test.describe('layout width preference applies to every shell', () => {
    test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: 1920, height: 1080 });
    });

    // The preference is shared state; every case leaves Standard behind even when it fails midway.
    test.afterEach(async ({ page }) => {
        await resetLayout(page);
    });

    test('Wide applies the wide shell on Dashboard, wizard steps and Settings', async ({ page }) => {
        await setLayout(page, 'wide');

        for (const url of ['/', '/career/setup/legacy', '/career/setup/deck', '/preferences']) {
            await page.goto(url);
            await page.locator('#app > *').first().waitFor();
            await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);
        }
    });

    test('Standard applies the standard shell on every surface', async ({ page }) => {
        await setLayout(page, 'standard');

        for (const url of ['/', '/career/setup/legacy', '/career/setup/deck', '/preferences']) {
            await page.goto(url);
            await page.locator('#app > *').first().waitFor();
            await expect(page.locator('#main')).toHaveClass(/max-w-6xl/);
        }
    });

    test('Compact applies the narrow shell on every surface', async ({ page }) => {
        await setLayout(page, 'compact');

        for (const url of ['/', '/career/setup/legacy', '/career/setup/deck', '/preferences']) {
            await page.goto(url);
            await page.locator('#app > *').first().waitFor();
            await expect(page.locator('#main')).toHaveClass(/max-w-3xl/);
        }
    });
});
