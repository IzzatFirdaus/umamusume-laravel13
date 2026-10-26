import { test, expect } from '@playwright/test';

/*
 * HFW structure pins for the create page (`runs-create.html`, slice 3 step 1.2).
 *
 * The page's copy, its one form card and its two actions already ship; what was never asserted is the
 * HFW's own shape: one `h1` carrying the HFW's copy, one filled action, the card heading above the
 * fields, and the actions at the end of the region. The behaviour (combobox, submit round trip) is
 * `runs.spec.ts`'s; this file does not repeat it.
 */

test('heads the page once with the HFW copy, one filled action, and the actions last', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // One h1, and it is the HFW's own line rather than an invented title.
    await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Create a career');
    await expect(page.getByText('New career', { exact: true }).first()).toBeVisible();

    // Region order: the card's heading is above the trainee field, and the actions sit after it.
    const heading = page.getByRole('heading', { name: 'Career', level: 2 });
    const trainee = page.getByRole('combobox', { name: 'Trainee or costume card name' });
    const actions = page.getByRole('button', { name: 'Create run' });
    const headingBox = await heading.boundingBox();
    const traineeBox = await trainee.boundingBox();
    const actionsBox = await actions.boundingBox();
    expect(headingBox?.y ?? 0).toBeLessThan(traineeBox?.y ?? 0);
    expect(traineeBox?.y ?? 0).toBeLessThan(actionsBox?.y ?? Number.MAX_SAFE_INTEGER);

    // One filled action, and Cancel is an outline beside it rather than a second primary.
    await expect(page.getByRole('button', { name: 'Create run' })).toHaveCount(1);
    const cancel = page.getByRole('link', { name: 'Cancel' });
    await expect(cancel).toBeVisible();
    const primaryColour = await actions.evaluate((el) => getComputedStyle(el).backgroundColor);
    const cancelColour = await cancel.evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(cancelColour).not.toBe(primaryColour);

    // The two optional fields name themselves optional, as the HFW's labels do.
    await expect(page.locator('label[for="create-notes"]')).toContainText('Notes (optional)');
});
