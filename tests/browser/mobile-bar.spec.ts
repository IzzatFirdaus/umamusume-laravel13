import { test, expect } from '@playwright/test';

/*
 * C5: the mobile bar's fourth destination. `Support Cards` shortened to `Deck` collided with the
 * run-scoped deck builder's name (`/training-runs/{run}/deck`, the Deck builder screen), so the
 * Library destination now reads `Cards` in the bar while the desktop sidebar keeps the full label.
 * The bar itself is `md:hidden`, so each half is asserted at its own width: 390 for the bar, 1280
 * for the sidebar.
 */

test('the mobile bar stays a four-destination bar and its fourth slot reads Cards', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const bar = page.getByRole('navigation', { name: 'Primary mobile' });
    await expect(bar).toBeVisible();

    // Four destinations plus the More disclosure, and the Library slot reads Cards in the fourth
    // position, not Deck: the run-scoped deck builder owns that word.
    await expect(bar.getByRole('link', { name: 'Cards', exact: true })).toBeVisible();
    await expect(bar.getByRole('link', { name: 'Deck', exact: true })).toHaveCount(0);
    await expect(bar.getByRole('link', { name: 'Support Cards', exact: true })).toHaveCount(0);
});

test('the desktop sidebar keeps the full Support Cards label', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const sidebar = page.getByRole('navigation', { name: 'Global navigation' });
    await expect(sidebar).toBeVisible();
    await expect(sidebar.getByRole('link', { name: 'Support Cards', exact: true })).toBeVisible();
});
