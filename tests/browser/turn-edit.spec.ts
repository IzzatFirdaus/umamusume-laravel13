import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * `C6b.2` (KI-144) — the turn-correction door at `/training-runs/{run}/turns/{turn}/edit`.
 *
 * The Cockpit's `Career tools` disclosure hosted the turn-correction form as the interim home for
 * writes the retired record screen owned. The disclosure cannot retire until each write has a door
 * of its own, and this walks the turn one: the Timeline row links to it, it renders the turn, and a
 * correction saved there is read back off the same door.
 *
 * No rendered surface creates a turn, so the fixture logs one over `runs.turns.store` through
 * `record-turns`, the shape `ura-panel.spec.ts` sets.
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

test('announces the turn on arrival and focuses a refused correction', async ({ page }) => {
    const runUrl = await createRun(page);
    const runId = runUrl.split('/').pop() as string;

    await recordTurns(page, runId, 1);

    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Edit turn 1' }).first().click();
    await page.waitForURL(/\/training-runs\/\d+\/turns\/\d+\/edit$/, WRITE);

    const response = await page.reload({ waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);

    // AUD-20261102-009 F-01: arriving on the page announces whose turn this is, before any write.
    await expect(page.getByRole('status').filter({ hasText: /Correcting turn 1 of/ })).toBeAttached();

    // AUD-20261102-009 F-02: a refused correction moves focus to its own alert, so a keyboard
    // Trainer meets the reason rather than being left at the button.
    await page.locator('#speed').fill('');
    await page.getByRole('button', { name: 'Save correction' }).click();

    const alert = page.getByRole('alert').filter({ hasText: 'The correction was not saved.' });
    await expect(alert).toBeVisible();
    await expect(alert).toBeFocused();
});

test('corrects a turn from the Timeline row and saves it on the door', async ({ page }) => {
    const runUrl = await createRun(page);
    const runId = runUrl.split('/').pop() as string;

    await recordTurns(page, runId, 1, { energy: '77' });

    // The Timeline row is the door's entrance: the write is reached from the entry it corrects.
    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const rowLink = page.getByRole('link', { name: 'Edit turn 1' }).first();
    await expect(rowLink).toBeVisible();
    await rowLink.click();
    await page.waitForURL(/\/training-runs\/\d+\/turns\/\d+\/edit$/, WRITE);

    // One `h1`, and it names the turn rather than the screen.
    await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Turn 1');

    // The recorded reading is prefilled rather than blank, so a correction starts from the fact.
    await expect(page.locator('#energy')).toHaveValue('77');

    // A correction writes and returns to the door it was submitted from (`back(fallback:)`).
    await page.locator('#energy').fill('88');
    await page.getByRole('button', { name: 'Save correction' }).click();
    await expect(page.getByText('Saved.')).toBeVisible();
    await expect(page.locator('#energy')).toHaveValue('88');
});
