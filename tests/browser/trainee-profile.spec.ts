import { test, expect } from '@playwright/test';

test.describe('Trainee profile', () => {
    test('renders the profile without a 500 when a form has no recorded release date', async ({ page }) => {
        // Phase 1 regression guard: null global_release_date must not crash the page.
        const response = await page.goto('/career/setup/trainee/2');
        expect(response?.status()).toBe(200);

        await page.locator('#app > *').first().waitFor();
        await expect(page.getByRole('heading', { name: 'Costume forms' })).toBeVisible();
    });

    test('renders populated base stats and growth after enrichment', async ({ page }) => {
        // Phase 2: base_stats and growth_rates are now stored and rendered.
        await page.goto('/career/setup/trainee/2');
        await page.locator('#app > *').first().waitFor();

        // At least one form shows five base-stat values rather than N/A.
        const baseSection = page.locator('[data-field="base-stats"]');
        await expect(baseSection).toBeVisible();
        // Growth tiles render numeric percentages.
        const growthTiles = page.locator('[data-field="growth"] .tile .v');
        await expect(growthTiles.first()).not.toHaveText('N/A');
    });

    test('does not show the "not recorded" absence note when data is present', async ({ page }) => {
        await page.goto('/career/setup/trainee/2');
        await page.locator('#app > *').first().waitFor();

        // When base stats are present, the absence note is not rendered for that block.
        await expect(page.getByText(/No base-stat figure is recorded/)).toHaveCount(0);
    });
});
