import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for Save Veteran (`SCR-VET-003`, plan §8 D16's write
 * half). `tests/Feature/SaveVeteranTest.php` asserts the resolved props and the whole validation boundary;
 * these cases assert what only a browser reaches: the tag state a sighted Trainer sees, the keyboard path
 * through a toggle and a save, the 44px sweep, and an axe scan.
 *
 * Fixture strategy. The run is created through the create form and finished through the run screen's own
 * status control (`select[name="status"]` plus `Change status`), because that is the path a Trainer takes
 * and `RecordVeteran` refuses anything but a Completed run. It is deleted over HTTP in `afterEach`
 * (`tests/utils/delete-run.ts`), which also removes the library row: the
 * migration makes a Veteran cascade on its run.
 *
 * The write gets 60s rather than the config's 15s, for the reason `career-race-decision.spec.ts` records:
 * `php artisan serve` is a single-process `php -S`, so a write queues behind whatever else is on the box.
 *
 * Harness. This spec WRITES rows: a run, and the Veteran its save creates. `playwright.config.ts` defaults
 * to port 8127, which serves `database/database.sqlite` - the shared dev file - so a bare
 * `npm run test:browser` here pollutes the state `tests/browser/veterans.spec.ts` asserts is empty. That is
 * KI-69 class 1, and until the harness is fixed in the config this spec must be run with
 * `PLAYWRIGHT_BASE_URL` pointed at a scratch database, as plan §4.1 item 7 has always required.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * A run at `/training-runs/{run}/veteran`, finished unless the case is about the refusal.
 *
 * `traineeName` is a seeded catalogue row. The fixture picks a distinct one per case so a run left behind by
 * a crashed spec cannot satisfy a later case by accident.
 */
async function openSaveVeteran(
    page: import('@playwright/test').Page,
    traineeName = 'Agnes Digital',
    finished = true,
): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(traineeName);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    // The bare record URL, not the Cockpit the create redirect lands on: the cases below address
    // sub-screens by appending to it (`${runUrl}/veteran`).
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    if (finished) {
        // Scoped to its own form: the Cockpit's header edits and its correction panel share the page, so
        // a name query is ambiguous and a label query worse.
        const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });

        await statusForm.locator('select[name="status"]').selectOption('Completed');
        await statusForm.getByRole('button', { name: 'Change status' }).click();
        await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    }

    await page.goto(`${runUrl}/veteran`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('files a career from the keyboard and the library reads the same tags back', async ({ page }) => {
    await openSaveVeteran(page);

    // The heading and the record-only contract, before any control.
    await expect(page.getByRole('heading', { name: 'Save Veteran' })).toBeVisible();
    await expect(page.getByText('Record only.')).toBeVisible();

    // A suggestion toggled by keyboard: focus, Enter, and the state is announced as well as shown.
    const speed = page.getByRole('button', { name: /^Speed/ });
    await speed.focus();
    await expect(speed).toBeFocused();
    await expect(speed).toHaveAttribute('aria-pressed', 'false');
    await page.keyboard.press('Enter');
    await expect(speed).toHaveAttribute('aria-pressed', 'true');

    // A word of the Trainer's own, added by keyboard through the same button a mouse uses.
    await page.locator('#custom-tag').fill('quiet early pace');
    await page.getByRole('button', { name: 'Add tag' }).click();
    await expect(page.getByText('Tag added: quiet early pace.')).toBeVisible();

    await page.locator('#veteran-notes').fill('Ran the autumn route twice.');
    await page.getByRole('button', { name: 'Save Veteran' }).click();

    // The write lands on the library row it created, and the confirmation is the flash, not a toast.
    await page.waitForURL(/\/veterans\/\d+$/, WRITE);
    await expect(page.getByText('Veteran saved: Agnes Digital.')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Agnes Digital' })).toBeVisible();
    await expect(page.getByText('quiet early pace')).toBeVisible();
    await expect(page.getByText('Ran the autumn route twice.')).toBeVisible();
});

test('names the held figures as held, and prints no recommendation anywhere', async ({ page }) => {
    await openSaveVeteran(page, 'Eishin Flash');

    await expect(page.getByRole('heading', { name: 'Not computed' })).toBeVisible();

    // Each held figure is `N/A` carrying the ruling that keeps it that way. The pair is addressed through
    // the `dt` element rather than `getByRole('term')`, which did not resolve for a `dt` inside the `div`
    // wrapper this definition list uses, and the ruling now rides on the disclosure's accessible name
    // rather than a `title` only a mouse could read.
    for (const label of ['Factor analysis', 'Legacy value', 'Best use']) {
        const cell = page.locator('dt', { hasText: label }).locator('xpath=following-sibling::dd[1]');
        const ruling = cell.locator('summary', { hasText: 'ADR-0020' });
        await expect(ruling).toContainText('N/A');
        await expect(ruling.locator('xpath=following-sibling::span')).toHaveText(/ADR-0020/);
    }

    // The brief's own recommendation wording must not appear, in any element or attribute.
    await expect(page.getByText('Best used for')).toHaveCount(0);
    await expect(page.getByText(/Excellent .* parent/)).toHaveCount(0);
    expect(await page.locator('body').innerText()).not.toContain('Best used for');
});

test('refuses a career that has not finished, with the door rather than a dead form', async ({ page }) => {
    await openSaveVeteran(page, 'Mejiro McQueen', false);

    await expect(page.getByRole('heading', { name: 'Nothing to file yet' })).toBeVisible();
    await expect(page.getByRole('alert')).toContainText('Active');

    // No form, so no save button that could only fail.
    await expect(page.getByRole('button', { name: 'Save Veteran' })).toHaveCount(0);
    await expect(page.getByRole('link', { name: 'Open the run list' })).toBeVisible();
});

test('sizes the save screen controls to the 44px contract', async ({ page }) => {
    await openSaveVeteran(page, 'Super Creek');

    const targets = [
        page.getByRole('button', { name: /^Speed/ }),
        page.getByRole('button', { name: 'Add tag' }),
        page.getByRole('button', { name: 'Save Veteran' }),
        page.locator('#custom-tag'),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, `${await target.evaluate((el) => el.tagName)} is below the 44px floor`).toBeGreaterThanOrEqual(44);
    }
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openSaveVeteran(page, 'Agnes Tachyon');

    const results = await buildAxe(page).analyze();

    expect(results.violations).toEqual([]);
});
