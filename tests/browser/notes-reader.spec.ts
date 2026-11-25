import { test, expect } from './support/base-test';
import { deleteRun } from '../utils/delete-run';

/*
 * R6-53: `training_runs.notes` was written from the create form, the snapshot form and the import
 * path, and read by no screen. The Cockpit now reads it in a native disclosure, and this case proves
 * the two states a Trainer can meet: a run that carries notes and a run that carries none.
 *
 * The fixture is built through the create form, which is one of the three writers, so the note under
 * test is the app's own stored text rather than a value injected past the request boundary.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: import('@playwright/test').Page, notes?: string): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });

    if (notes !== undefined) {
        await page.locator('#create-notes').fill(notes);
    }

    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { timeout: 60_000 });

    const cockpitUrl = page.url();
    createdRunUrls.push(cockpitUrl.replace(/\/cockpit$/, ''));

    return cockpitUrl;
}

test('reads the stored run notes in a disclosure and names their absence', async ({ page }) => {
    const note = 'Read off 68 client frames. The finals are five turns away.';

    await createRun(page, note);

    const panel = page.locator('[data-region="run-notes"]');
    await expect(panel).toBeVisible();
    await expect(panel.locator('summary')).toHaveText('Notes');
    // A native disclosure starts closed: the note is one press away, not a wall of text in the rail.
    await expect(panel).not.toHaveAttribute('open', '');
    await panel.locator('summary').click();
    await expect(panel).toHaveAttribute('open', '');
    await expect(panel).toContainText(note);

    await createRun(page);

    await expect(page.locator('[data-region="run-notes"]')).toHaveCount(0);
    await expect(page.getByText('No notes recorded.')).toBeVisible();
});
