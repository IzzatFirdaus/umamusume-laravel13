import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

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
    await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
    await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });

    return runUrl;
}

test('lists the saved pair as the unit, with its three values', async ({ page }) => {
    const runUrl = await runWithPair(page);
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The three regions `legacy.html` draws. "Saved legacies" is the page's own new unit and sits
    // first; "What a legacy is worth" and "Parents available" already live in the reference aside, so
    // they are read there rather than looked for twice on the page.
    const saved = page.getByRole('heading', { name: 'Saved legacies', level: 2 });
    const library = page.getByRole('heading', { name: 'Veteran library', level: 2 });
    const reference = page.getByRole('complementary', { name: 'Legacy reference' });
    await expect(saved).toBeVisible();
    await expect(library).toBeVisible();
    await expect(reference.getByRole('heading', { name: 'What a legacy is worth', level: 2 })).toBeVisible();
    await expect(reference.getByRole('heading', { name: 'Parents available', level: 2 })).toBeVisible();

    const savedTop = (await saved.boundingBox())?.y ?? 0;
    const libraryTop = (await library.boundingBox())?.y ?? 0;
    expect(savedTop).toBeLessThan(libraryTop);

    // The pair is a row, keyed by the run that carries it.
    const pair = page.locator(`[data-pair-run="${runId}"]`);
    await expect(pair).toBeVisible();

    // The three values, each as its own labelled row, and each printed (never a bare dash).
    for (const label of ['Blue grades', 'Pink range', 'Unique-skill hints']) {
        await expect(pair.getByText(label, { exact: true })).toBeVisible();
    }

    // The worth table is three rows: blue, pink, and the hint/skill family.
    await expect(reference.locator('table tbody tr')).toHaveCount(3);

    // One filled action on the page, and it is Compare (the slice's ruling).
    await expect(page.locator('a.bg-primary-action')).toHaveCount(1);
    await expect(page.getByRole('link', { name: 'Compare two pairs' })).toHaveClass(/bg-primary-action/);
});
