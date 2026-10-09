import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-019, the Career Result
 * (`SCR-CAR-018`, plan §8 D15). `CareerResultTest` pins the props — the four race counts, the
 * no-target deficit, the named Save Veteran absence. These cases assert what only a browser
 * reaches: that the two unfinished states are told apart on screen, that a retired career is
 * offered no Cockpit door, the 44px sweep, the 320px reflow and an axe scan against `#app`.
 *
 * The run is created Active by the run screen, and its status is moved with the run screen's own
 * status form — the write that already owns the column. Writes get 60s because `php artisan serve`
 * is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A fresh run, left Active, with its record-screen URL remembered for cleanup. */
async function createRun(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    // The bare record URL, not the Cockpit the create redirect lands on: the cases below address
    // sub-screens by appending to it (`${runUrl}/result`).
    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));
}

/**
 * Move the run to `Completed` or `Retired` through the Cockpit's own status form.
 *
 * The select is scoped to the form that owns the "Change status" button: the Cockpit's header edits and
 * its correction panel share the page, and an unscoped locator drives whichever one comes first in
 * document order, which may have no `Completed` option to choose.
 */
async function setStatus(page: import('@playwright/test').Page, status: 'Completed' | 'Retired'): Promise<void> {
    await page.goto(createdRunUrls[createdRunUrls.length - 1], { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const form = page.locator('form', { has: page.getByRole('button', { name: 'Change status' }) });
    await form.locator('select[name="status"]').selectOption(status);
    await Promise.all([
        page.waitForLoadState('domcontentloaded'),
        form.getByRole('button', { name: 'Change status' }).click(),
    ]);
}

test('an Active career is told it is still running, and offered the Cockpit', async ({ page }) => {
    await createRun(page);
    await page.goto(`${createdRunUrls[createdRunUrls.length - 1]}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'This career is still running' })).toBeVisible();
    await expect(page.getByText(/still running, so there is no result to read yet/)).toBeVisible();

    // The one state that still has a turn to decide is the one that keeps its Cockpit door.
    await expect(page.getByRole('link', { name: 'Back to the Cockpit' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Final build' })).toHaveCount(0);
});

test('a Retired career is a different absence, with no Cockpit door anywhere on the page', async ({ page }) => {
    await createRun(page);
    await setStatus(page, 'Retired');
    await page.goto(`${createdRunUrls[createdRunUrls.length - 1]}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'This career was retired' })).toBeVisible();
    await expect(page.getByText(/retired before it finished/)).toBeVisible();

    // The correction this spec exists for: a retired career has no next turn, so the page must not
    // offer a decision screen. Both the primary door and the footer link are checked.
    await expect(page.locator('a[href$="/cockpit"]')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Final build' })).toHaveCount(0);

    // It does offer the read-only doors the empty state carries.
    await expect(page.getByRole('link', { name: 'Career Timeline' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Run record' }).first()).toBeVisible();
});

test('a Completed career renders the three sections and the Save Veteran door', async ({ page }) => {
    await createRun(page);
    await setStatus(page, 'Completed');
    await page.goto(`${createdRunUrls[createdRunUrls.length - 1]}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Final build' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Race history' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Scenario result' })).toBeVisible();

    // No build target was entered, so every stat reads N/A against no target rather than measuring
    // the stat against zero.
    await expect(page.getByText(/No build target was set for this run/).first()).toBeVisible();

    // The door to `SCREEN-020` is real. This case asserted the opposite until 2026-10-08: it expected the
    // D15 sentence "Saving a Veteran belongs to the Veteran library slice" and a count of zero links,
    // which was true only while D16 was unlanded. `7b04b6c` landed `runs.veteran` and `RecordVeteran`, and
    // `ResultController::saveVeteranSection()` has offered the link with its reason since — so the screen
    // was right and this assertion was the stale artifact (`SCREEN_SPEC.md` SCR-CAR-018: "Save Veteran is
    // a door to `SCR-VET-003`").
    const next = page.getByRole('region', { name: 'What you can do next' });
    await expect(next.getByRole('link', { name: 'Save Veteran' })).toBeVisible();
    await expect(
        next.getByText(/Adds your own tags and note to this career in the Veteran library/),
    ).toBeVisible();

    // The ruleset line is an absence with its reason.
    await expect(page.getByText(/No source defines a Global ruleset version/)).toBeVisible();

    // No aggregate the slice forbids can reach the page.
    await expect(page.locator('main')).not.toContainText('Build quality');
    await expect(page.locator('main')).not.toContainText('completion');
});

test('keeps the 44px floor and reflows at 320 px', async ({ page }) => {
    await createRun(page);
    await setStatus(page, 'Completed');
    await page.goto(`${createdRunUrls[createdRunUrls.length - 1]}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The page's own doors are 44px targets (WCAG 2.2 SC 2.5.8). The two inside the actions
    // section are scoped to it because `CareerLayout` renders a "Run record" link of its own, and
    // the footer link is named by its label (unique on this state) for the same reason.
    const actions = page.getByRole('region', { name: 'What you can do next' });

    for (const target of [
        actions.getByRole('link', { name: 'View Career Timeline' }),
        actions.getByRole('link', { name: 'Run record' }),
        page.getByRole('link', { name: 'Back to Cockpit' }),
    ]) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a result-page link is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('heading', { name: 'Final build' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Race history' })).toBeVisible();
});

test('passes the axe A + AA scan on a completed career result', async ({ page }) => {
    await createRun(page);
    await setStatus(page, 'Completed');
    await page.goto(`${createdRunUrls[createdRunUrls.length - 1]}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await buildAxe(page).analyze();
});
