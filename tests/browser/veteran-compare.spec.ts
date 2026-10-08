import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for the Veteran comparison (`SCR-VET-004`, plan §8
 * D16). `tests/Feature/VeteranCompareTest.php` asserts the props, the column order, the four-at-once cap
 * and the refusal of an id that is not in the library; these cases assert what only a browser reaches: the
 * aligned table a Trainer reads, the picker's keyboard path, the 44px sweep and an axe scan.
 *
 * Fixture strategy. Each career is created through the create form, finished through the run screen's own
 * status control, and filed through Save Veteran, because that is the path a Trainer takes and it is the
 * only path that puts a row in `veterans`. Runs are deleted over HTTP in
 * `afterEach` (`tests/utils/delete-run.ts`), which cascades the library row with the run.
 *
 * The four-at-once cap is not repeated in a browser case here on purpose: proving it in the DOM needs five
 * filed careers, five create-and-file cycles, and it would assert a rule the request layer already refuses
 * in `VeteranCompareTest`. The disabled checkbox is asserted where it is cheap, on the two-career fixture.
 *
 * Harness. Like `career-save-veteran.spec.ts` this WRITES rows, so it must run against a scratch database via
 * `PLAYWRIGHT_BASE_URL` and not against the port 8127 default, which serves the shared dev file the
 * empty-library assertion in `veterans.spec.ts` depends on (KI-69 class 1).
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * Files one career into the library, by the path a Trainer takes.
 *
 * `tag` is nullable on purpose: the comparison is only honest about an untagged career if a career without
 * tags is actually on screen, so the second fixture files none and the row's "No tags recorded" is a fact
 * rather than a phrase this spec never earns.
 */
async function fileCareer(
    page: import('@playwright/test').Page,
    traineeName: string,
    tag: string | null,
): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(traineeName);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);

    const runUrl = page.url();
    createdRunUrls.push(runUrl);

    // Scoped to its own form: `runs.show` carries two more `select[name="status"]` controls (RacePanel's
    // entry statuses and one per skill row), so a name query is ambiguous and a label query worse.
    const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });

    await statusForm.locator('select[name="status"]').selectOption('Completed');
    await statusForm.getByRole('button', { name: 'Change status' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/veteran`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    if (tag !== null) {
        await page.getByRole('button', { name: new RegExp(`^${tag}`) }).click();
    }
    await page.getByRole('button', { name: 'Save Veteran' }).click();
    await page.waitForURL(/\/veterans\/\d+$/, WRITE);
}

test('lines careers up with one property per row, reached from the library row', async ({ page }) => {
    await fileCareer(page, 'Agnes Digital', 'Speed');
    await fileCareer(page, 'Eishin Flash', null);

    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The library's own door, labelled for what it opens rather than left to a bare "Compare", and scoped to
    // one row so the entry career is fixed rather than whatever the default order puts first.
    await page.getByRole('listitem').filter({ hasText: 'Eishin Flash' })
        .getByRole('link', { name: 'Compare this career' })
        .click();
    await page.waitForURL(/\/veterans\/compare\?veterans\[\]=\d+/, WRITE);

    // The *other* career is picked from the keyboard: the entry career is already in `picked`, so ticking it
    // again would deselect it and leave the Compare button with nothing to act on.
    await page.getByRole('checkbox', { name: 'Agnes Digital' }).check();
    await page.getByRole('button', { name: 'Compare' }).click();
    // The path, not the query. The library row's door is a server-rendered href and lands as
    // `?veterans[]=N`; this button is `router.get` with an array value, and Inertia serialises that as
    // `?veterans%5B0%5D=N`. PHP builds the same array from either spelling, so the wire format is not
    // the property — the two columns below are, and they only render if both picks arrived.
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);

    // A career's name is printed once on this page, in its `th scope="col"`, so both columns are asserted
    // through that role and the section's own count agrees with the number of careers filed.
    await expect(page.getByRole('heading', { name: '2 careers side by side' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Eishin Flash' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Agnes Digital' })).toBeVisible();

    // §46: identical properties aligned vertically. One `th` per property, and the properties a career
    // actually records.
    const rows = await page.getByRole('rowheader').allTextContents();
    expect(rows).toEqual(expect.arrayContaining(['Scenario', 'Turns logged', 'Speed', 'Tags', 'Note']));
    expect(rows.length).toBeGreaterThan(10);

    // A row that holds a value and a row that holds none are told apart in words, not by blank space.
    await expect(page.getByText('No tags recorded').first()).toBeVisible();

    // And the rows the brief asks for that no column answers are named once, not scored per career.
    await expect(page.getByRole('heading', { name: 'NOT IN THIS BUILD' })).toBeVisible();
    await expect(page.getByText(/Inheritance usefulness and a compatibility calculation/)).toBeVisible();
});

test('says nothing is selected before anything is picked, rather than showing an empty table', async ({ page }) => {
    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Nothing selected' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Open the library' })).toBeVisible();

    // The compare button has nothing to act on, so it is inert rather than promising a blank table.
    await expect(page.getByRole('button', { name: 'Compare' })).toBeDisabled();
});

test('sizes the comparison controls to the 44px contract', async ({ page }) => {
    await fileCareer(page, 'Super Creek', 'Speed');

    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const targets = [
        // The picker's tap target is the label row that carries the input, not the native 24px box inside
        // it: the row is `min-h-11` and clicking anywhere on it toggles the pick.
        page.locator('label').filter({ has: page.getByRole('checkbox', { name: 'Super Creek' }) }),
        page.getByRole('button', { name: 'Compare' }),
        page.getByRole('link', { name: 'Open the library' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a comparison control is below the 44px floor').toBeGreaterThanOrEqual(44);
    }
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await fileCareer(page, 'Admire Vega', 'Speed');

    // Scanned with a column rendered rather than on the empty picker: the aligned table is what this screen
    // exists to print, and a career's Japanese name only appears inside its column head.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Compare this career' }).first().click();
    await page.waitForURL(/\/veterans\/compare\?veterans\[\]=\d+/, WRITE);

    const results = await buildAxe(page).analyze();

    expect(results.violations).toEqual([]);
});
