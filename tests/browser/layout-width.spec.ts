import { test, expect } from '@playwright/test';

/*
 * Cross-layout evidence for the Layout width preference (DL.14 / PLAN 6B.15). The feature test
 * `PreferenceControlsTest` pins the shared prop; these assert the class is actually applied on every
 * shell at 1920 x 1080.
 *
 * The four surfaces that share the preference: Dashboard (AppLayout), Legacy step 4 and Support
 * deck step 5 (SetupLayout), and Settings (AppLayout). Since all three layouts render through
 * AppLayout's `<main>`, `#main` is the stable container across every shell, and it carries the
 * shared `layoutWidthClass` from `useLayout()`.
 */

test.describe('layout width preference applies to every shell', () => {
    test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: 1920, height: 1080 });
    });

    test('Wide applies the wide shell on Dashboard, wizard steps and Settings', async ({ page }) => {
        // Set the preference to Wide via the Settings screen.
        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();
        await page.getByRole('radio', { name: 'Wide', exact: true }).check();
        await page.getByRole('button', { name: 'Save preferences' }).click();
        await expect(page.getByRole('status')).toHaveText('Preferences saved.');

        // 1. Dashboard — AppLayout
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);

        // 2. Legacy — SetupLayout, step 4 of 6
        await page.goto('/career/setup/legacy');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);

        // 3. Support deck — SetupLayout, step 5 of 6
        await page.goto('/career/setup/deck');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);

        // 4. Settings — AppLayout (the page where the preference is set)
        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);

        // Reset to Standard so the suite leaves the shared store as it found it.
        await page.getByRole('radio', { name: 'Standard', exact: true }).check();
        await page.getByRole('button', { name: 'Save preferences' }).click();
        await expect(page.getByRole('status')).toHaveText('Preferences saved.');
    });

    test('Standard applies the standard shell on every surface', async ({ page }) => {
        // The shared store defaults to Standard; assert the fallback path is consistent.
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-6xl/);

        await page.goto('/career/setup/legacy');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-6xl/);

        await page.goto('/career/setup/deck');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-6xl/);

        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-6xl/);
    });

    test('Compact applies the narrow shell on every surface', async ({ page }) => {
        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();
        await page.getByRole('radio', { name: 'Compact' }).check();
        await page.getByRole('button', { name: 'Save preferences' }).click();
        await expect(page.getByRole('status')).toHaveText('Preferences saved.');

        await page.goto('/');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-3xl/);

        await page.goto('/career/setup/legacy');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-3xl/);

        await page.goto('/career/setup/deck');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#main')).toHaveClass(/max-w-3xl/);

        // Reset to Standard.
        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();
        await page.getByRole('radio', { name: 'Standard', exact: true }).check();
        await page.getByRole('button', { name: 'Save preferences' }).click();
        await expect(page.getByRole('status')).toHaveText('Preferences saved.');
    });
});
