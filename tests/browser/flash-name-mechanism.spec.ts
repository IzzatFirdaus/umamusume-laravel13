import { test, expect } from '@playwright/test';

// Mechanism probe. Confirms WHY getByRole('status', { name }) fails while
// getByRole('button', { name }) works: the `status` role is not an ARIA
// "name from content" role, so Playwright's accessible-name computation for
// getByRole matching returns empty for a content-derived name, but still
// matches an explicit aria-label. Static page, no save round trip.

test('status name-matching mechanism', async ({ page }) => {
    await page.setContent(`
        <p role="status" id="from-content">Preferences saved.</p>
        <p role="status" id="from-label" aria-label="Explicit label"></p>
        <button id="btn" type="button">Save preferences</button>
    `);

    const probe = async (fn: () => Promise<unknown>): Promise<string> => {
        try {
            await fn();
            return 'PASS';
        } catch {
            return 'FAIL';
        }
    };

    const results = {
        statusNameFromContent: await probe(() => expect(page.getByRole('status', { name: 'Preferences saved.' })).toBeVisible()),
        statusExplicitAriaLabel: await probe(() => expect(page.getByRole('status', { name: 'Explicit label' })).toBeVisible()),
        buttonNameFromContent: await probe(() => expect(page.getByRole('button', { name: 'Save preferences' })).toBeVisible()),
        statusRoleOnly: await probe(() => expect(page.getByRole('status')).toBeVisible()),
    };

    // eslint-disable-next-line no-console
    console.log('MECH_PROBE', JSON.stringify(results, null, 2));

    expect(results.statusRoleOnly).toBe('PASS');
});
