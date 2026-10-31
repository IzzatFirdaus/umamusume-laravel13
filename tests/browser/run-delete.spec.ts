import { test, expect, type Page } from '@playwright/test';

/*
 * `C6b.3` (KI-144) — the delete-run confirmation door at `/training-runs/{run}/delete`.
 *
 * The Cockpit's `Career tools` disclosure hosted the destructive write. Its door states what will be
 * lost and refuses until the Trainer says so, and `runs.destroy` refuses a request with no
 * confirmation flag. This walks that refusal and the delete.
 *
 * The run is created by this case and removed by it, so there is no teardown helper: the delete IS
 * the subject, and a cleanup delete after it would be asserting the same write twice.
 */

const WRITE = { timeout: 60_000 };

async function createRun(page: Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.selectOption('select[name="status"]', { value: 'Active' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    return page.url().replace(/\/cockpit$/, '');
}

test('refuses the delete until the checkbox is ticked, then deletes the run', async ({ page }) => {
    const runUrl = await createRun(page);

    // The Cockpit foots the page with the door, so the write is reachable without a hidden disclosure.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const door = page.getByRole('link', { name: 'Delete this run' });
    await expect(door).toBeVisible();
    await door.click();
    await page.waitForURL(/\/training-runs\/\d+\/delete$/, WRITE);

    // The page states what will be lost before it offers the action.
    await expect(page.getByRole('heading', { name: 'This cannot be undone' })).toBeVisible();

    // Refused until the confirmation is ticked: the action is disabled, not merely discouraged.
    const submit = page.getByRole('button', { name: 'Delete this career' });
    await expect(submit).toBeDisabled();
    await page.locator('#confirmed').check();
    await expect(submit).toBeEnabled();

    await submit.click();

    // The delete lands on the run list, which is the only screen a deleted run can answer with.
    await page.waitForURL(/\/training-runs$/, WRITE);
    await expect(page.getByText('Run deleted.')).toBeVisible();
});
