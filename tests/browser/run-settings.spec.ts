import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * `C6b.1` (KI-144) — the run-identity door at `/training-runs/{run}/settings`.
 *
 * The Cockpit's `Career tools` disclosure hosted the run-identity forms as an interim home for
 * writes the retired record screen owned. The disclosure cannot retire until each write has a door
 * of its own, and this walks the identity one: the page renders, its forms post to the run's own
 * update route, and a saved field is read back off the Cockpit.
 *
 * The props and the write contract are asserted in `tests/Feature/RunSettingsTest.php`; this case
 * covers what only a browser can show — that the door is reachable, laid out, and that saving from
 * it does not bounce the Trainer to a screen they did not submit from.
 */

const WRITE = { timeout: 60_000 };
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.selectOption('select[name="status"]', { value: 'Active' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    return runUrl;
}

test('walks the run-identity door and saves a field without leaving it', async ({ page }) => {
    const runUrl = await createRun(page);

    // The door is reachable from the Cockpit, so the disclosure's copy has a real alternative.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const door = page.getByRole('link', { name: 'Run settings' });
    await expect(door).toBeVisible();
    await door.click();
    await page.waitForURL(/\/training-runs\/\d+\/settings$/, WRITE);

    // One `h1`, and it names the run rather than the screen.
    await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Rice Shower');

    // The identity form is the page's write, and its fields are the run's own.
    const rarity = page.locator('#trainee_rarity');
    await expect(rarity).toBeVisible();
    await rarity.selectOption('3');
    await page.getByRole('button', { name: 'Save identity' }).click();

    // `back(fallback:)` on `runs.update` means the save returns to this door, not to the Cockpit.
    await expect(page.getByText('Saved.')).toBeVisible();

    // The value really landed. It is read back on the door itself, because `C6b.7` deleted the
    // Cockpit's duplicate identity block: the door is now the only surface that shows this field,
    // so a reload of the door is the honest round-trip.
    await page.goto(`${runUrl}/settings`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#trainee_rarity')).toHaveValue('3');
});
