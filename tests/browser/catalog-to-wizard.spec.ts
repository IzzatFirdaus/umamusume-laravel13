import { test, expect } from '@playwright/test';

/*
 * Slice 4.3: the catalog detail's "New run" action carries the selected trainee into `SetupDraft`
 * and opens the six-step wizard at its first step. `CatalogStartCareerTest` pins the draft's
 * read-back, its survival across steps and the absent run row; these assert what only a browser
 * reaches: the button posts, the wizard's scenario step receives the visit, the trainee step prints
 * her as the stored choice, and the Careers list is still empty because the carry wrote no run.
 */

test('carries the selected trainee from the catalog into the wizard', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('button', { name: 'New career' }).click();
    await page.waitForURL(/\/career\/setup\/scenario$/);

    // The carried choice reads back on the trainee step: the same stored-choice line a Trainer who
    // picked her inside the wizard sees (D5's read-back rule).
    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText('Stored choice: Special Week')).toBeVisible();

    // And the carry wrote no run: the screen that owns the run list still shows its empty state.
    await page.goto('/training-runs', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText('No runs yet')).toBeVisible();
});
