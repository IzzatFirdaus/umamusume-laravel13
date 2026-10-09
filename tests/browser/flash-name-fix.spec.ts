import { test, expect } from '@playwright/test';

// Fix-verification probe. The diagnosis is settled: getByRole('status') matches
// the flash region, but getByRole('status', { name }) never matches it because
// the `status` role's accessible name is content-derived and Playwright's
// name-matching does not apply to it. This verifies, against one real save, the
// candidate replacements for preferences.spec.ts:53 that keep the status-region
// assertion AND check the message text.

test('flash banner fix candidates', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();

    // Anchor on the text, which is known to resolve, so every candidate is
    // evaluated against the same persisted flash.
    await expect(page.getByText('Preferences saved.')).toBeVisible();

    const probe = async (fn: () => Promise<unknown>): Promise<string> => {
        try {
            await fn();
            return 'PASS';
        } catch {
            return 'FAIL';
        }
    };

    const results = {
        // Candidate A: role-only visibility + exact text content.
        rolePlusText: await probe(async () => {
            const status = page.getByRole('status');
            await expect(status).toBeVisible();
            await expect(status).toHaveText('Preferences saved.');
        }),
        // Candidate B: role filtered by contained text.
        roleFilterHasText: await probe(() => expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible()),
        // Candidate C: the current failing locator, for contrast.
        currentNameLocator: await probe(() => expect(page.getByRole('status', { name: 'Preferences saved.' })).toBeVisible()),
    };

    // eslint-disable-next-line no-console
    console.log('FIX_PROBE', JSON.stringify(results, null, 2));

    expect(results.rolePlusText, 'candidate A must pass').toBe('PASS');
    expect(results.roleFilterHasText, 'candidate B must pass').toBe('PASS');
});
