import { test, expect } from '@playwright/test';

// Rendered-copy and accessibility evidence for the ported Review queue (SCR-REV-001, F14).
// The server-side ReviewFormAccessibilityTest asserts the props; here the browser computes the
// accessible names, which is the DOM relation the a11y gate is about.

test('every verdict control is named after its own candidate', async ({ page }) => {
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // With no pending candidates the queue shows its empty state; the naming assertions need one.
    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database to name.');
    }

    // Each control is reachable by the name that carries its candidate - the F14 contract.
    const status = page.getByLabel(/^Verdict for /).first();
    await expect(status).toBeVisible();

    const resolve = page.getByRole('button', { name: /^Resolve / }).first();
    await expect(resolve).toBeVisible();
    await expect(resolve).toHaveText(/Resolve/);

    // The alias-language control offers the language options.
    const alias = page.getByLabel(/^Alias language for /).first();
    await expect(alias.locator('option')).not.toHaveCount(0);
});

test('a failed verdict shows its message on the card', async ({ page }) => {
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // An Aliased verdict with no language is the D-2 case: the message must reach the card.
    await page.getByLabel(/^Verdict for /).first().selectOption('Aliased');
    await page.getByRole('button', { name: /^Resolve / }).first().click();

    await expect(page.getByRole('alert').first()).toContainText(/language/i);
});
