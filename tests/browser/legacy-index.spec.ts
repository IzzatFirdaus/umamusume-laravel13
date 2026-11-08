import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';

/*
 * S7.1 — the Legacy Lab's browse surface, measured on `legacy.html`.
 *
 * The page's unit is the **saved pair**, not the Veteran row: a Legacy Select is one object (two
 * parents plus the sparks they pass on) stored as one `training_runs.legacy_selection` column, so the
 * rows the HFW draws are runs that carry a selection. `LegacyController@index` projects them
 * additively; this file asserts what the browser shows.
 *
 * The fixture is a run created through the create form and given a parent through the builder — the
 * same two writes `legacy.spec.ts` uses — because a pair cannot be seeded from the browser otherwise.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A run with one parent assigned, so its `legacy_selection` carries a pair. */
async function runWithPair(page: Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByLabel('Assign to Parent A').selectOption({ index: 0 });
    await page.getByRole('button', { name: 'Save legacy' }).click();
    await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });

    return runUrl;
}

test('lists the saved pair as a table row and states what a legacy is worth', async ({ page }) => {
    const runUrl = await runWithPair(page);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The three regions the HFW draws (`legacy.html:70-113`): the pair table on the left, the worth and
    // parent cards in the reference column.
    const saved = page.getByRole('heading', { name: 'Saved legacies', level: 2 });
    const reference = page.getByRole('complementary', { name: 'Legacy reference' });
    await expect(saved).toBeVisible();
    await expect(reference.getByRole('heading', { name: 'What a legacy is worth', level: 2 })).toBeVisible();
    await expect(reference.getByRole('heading', { name: 'Parents available', level: 2 })).toBeVisible();

    // Owner ruling 2026-10-28: the veteran library, its filters and its pager are not on this page.
    await expect(page.getByRole('heading', { name: 'Veteran library', level: 2 })).toHaveCount(0);
    await expect(page.getByLabel('Tag')).toHaveCount(0);

    // The wireframe's columns, in its order.
    const table = page.locator('table').filter({ has: page.getByRole('columnheader', { name: 'Parent A' }) });
    await expect(table.getByRole('columnheader', { name: 'Legacy' })).toBeVisible();
    await expect(table.getByRole('columnheader', { name: 'Parent B' })).toBeVisible();
    await expect(table.getByRole('columnheader', { name: 'Blue sparks' })).toBeVisible();

    // The pair is one row, keyed by the run that carries it, and its Open door is the builder.
    const row = table.locator('tbody tr').filter({ hasText: `run #${runId}` });
    await expect(row).toBeVisible();
    await expect(row.getByRole('link', { name: 'Open' })).toHaveAttribute('href', new RegExp(`/legacy/${runId}$`));

    // The worth table is the wireframe's six rows.
    const worth = reference.getByRole('region', { name: 'What a legacy is worth' });
    await expect(worth.locator('table tbody tr')).toHaveCount(6);

    // One filled action on the page, and it is Compare (the slice's ruling).
    await expect(page.locator('a.bg-primary-action')).toHaveCount(1);
    await expect(page.getByRole('link', { name: 'Compare two pairs' })).toHaveClass(/bg-primary-action/);
});

/**
 * A run the walk-through can write into, created through the app's own form the way `runWithPair`
 * does, but stopped at the builder rather than confirmed. The two helpers share the create path so
 * the walk exercises the same code the browse surface's `Open` door leads to.
 */
async function runWithDraft(page: Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    // `S-A-P02.6`: the Builder's route is `legacy/{run}` (`routes/web.php`, `legacy.builder`), not
    // `/training-runs/{id}/legacy`. This fixture built the second form from `runUrl`, so it navigated to a
    // route that does not exist and waited on `#app > *` until the case timed out — a 404 shell renders no
    // app root. Verified pre-existing (the same construction was at `HEAD` before this pass), and fixed
    // here because the row label rename this pass makes cannot be checked through a case that never loads.
    const runId = runUrl.split('/').pop() ?? '';

    await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('records a parent with a Spark, picks the primary, and reads it back on the compare surface', async ({ page }) => {
    // `S-A-P02.6`: the parent list is the **Veteran library** — `LegacyController::roster()` maps stored
    // `veterans` rows, not the catalogue — and the browser scratch database starts with **zero** of them
    // (`global-setup.ts` runs `migrate --seed`, and no seeder files a career). A parent therefore has to be
    // created by filing one, which is the only path the product offers: `runs.veteran.store` is reached
    // from a completed run. Filed *before* the draft run, because the Builder's roster is read when the
    // page loads — creating the Veteran afterwards would need a second navigation and the draft's own
    // `Save legacy` would already have left the screen. The case previously named `Gold Ship` outright,
    // which resolves on no database this repo can build.
    createdRunUrls.push(await fileCareer(page, 'Silence Suzuka', null));

    const runUrl = await runWithDraft(page);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // Assign a parent and give her one Spark, then mark it primary. The pick is the wireframe's radio
    // (`legacy-builder.html:78`): the group is named for the parent and each radio is named by the
    // Spark in its own row, so "Primary" alone is not four identical names in one group.
    const parentSelect = page.getByLabel('Assign to Parent A');
    const parentOption = await parentSelect.locator('option').nth(1).textContent();
    const parentName = (parentOption ?? '').trim();
    expect(parentName, 'the Veteran library offers no parent to assign').not.toBe('');

    await parentSelect.selectOption({ label: parentName });
    await page.getByLabel('Grandparent A1', { exact: true }).fill('Tokai Teio');
    await page.getByLabel('Own rank').first().fill('3');
    await page.getByRole('button', { name: 'Add Spark to Parent A' }).click();
    await page.getByLabel('Kind').first().selectOption('blue');
    // `Target` is the picker's combobox since `S-A-P02.3` (`S-A-P02.4`): a bare `fill()` types a
    // filter and never commits a value, so the selection goes through the option list.
    // The row's label is `Target` tree-wide since `S-A-P02.6`; this surface said `Applies to` alone.
    await page.getByLabel('Target').first().click();
    await page.getByLabel('Target').first().fill('Speed');
    await page.locator('[role="option"]', { hasText: 'Speed' }).first().click();
    await page.getByLabel('Stars', { exact: true }).first().fill('3');
    await page.getByRole('radio', { name: new RegExp(`Primary spark for ${parentName}: blue Speed`, 'i') }).check();

    await page.getByRole('button', { name: 'Save legacy' }).click();
    await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });

    // The write lands on the compare surface, and the parent the Trainer assigned is the one that
    // shows up. The `thead` carries the trainee (`column.trainee`), not the parent; the parent name
    // is the "Parent A" row, which is what a Trainer opened the page to check.
    const table = page.getByRole('region', { name: /side by side/i }).getByRole('table');
    await expect(table).toBeVisible();
    // `S-A-P02.6`: the table carries one row per *field* for each parent — name, rank, ancestors, then a
    // row per Spark kind — so `/Parent A/` resolves to eight rows and a bare assertion is ambiguous. The
    // row this case is about is the one naming the parent, and that row is the one holding the name.
    await expect(table.getByRole('row', { name: new RegExp(`Parent A ${parentName}`, 'i') })).toBeVisible();
});
