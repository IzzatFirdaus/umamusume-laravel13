import { test, expect } from '@playwright/test';

// Rendered-copy evidence for the ported Preferences screen (SCR-SYS-002). The server-side
// PreferenceControlsTest asserts the props; this asserts what the browser actually shows.

test('Preferences renders its heading, both controls and the shell nav', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Preferences', level: 2 })).toBeVisible();

    // The theme control offers the three authorized values (follow-the-OS, light, dark).
    const theme = page.getByLabel('Theme');
    await expect(theme).toBeVisible();
    await expect(theme.locator('option')).toHaveText([
        'Follow the system',
        'Light',
        'Dark',
    ]);

    await expect(page.getByRole('checkbox', { name: 'Numeric failure estimate' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save preferences' })).toBeVisible();

    // The shell's navigation is present (the sidebar on desktop).
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
});

test('saving a preference confirms in place without a full reload', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();

    // The flash status is rendered by the shell after the Inertia visit.
    await expect(page.getByText('Preferences saved.')).toBeVisible();
});
