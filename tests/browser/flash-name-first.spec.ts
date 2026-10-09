import { test, expect } from '@playwright/test';

// Decisive experiment. The save is slow in this sandbox (~9.4s measured), and both assertions share
// the default 15s locator budget. preferences.spec.ts:53 polls getByRole AFTER :48's getByText has
// already consumed part of that budget, so it starts with less headroom. This runs the exact owner's
// locator first, with a 30s budget, before anything else polls.

test('flash banner exact-name, polled first with 30s budget', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();

    const start = Date.now();
    await expect(page.getByRole('status', { name: 'Preferences saved.' })).toBeVisible({ timeout: 30_000 });
    const elapsed = Date.now() - start;

    // eslint-disable-next-line no-console
    console.log('FLASH_NAME_FIRST', JSON.stringify({ elapsedMs: elapsed, ok: true }));
});