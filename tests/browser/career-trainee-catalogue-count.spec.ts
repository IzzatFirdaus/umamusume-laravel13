import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * F-02 — the catalogue count follows the filter.
 *
 * Walk 8's finding: `/career/setup/trainee` opens with `66 trainees are catalogued.`, then a search for
 * `Rice Shower` re-renders the lede as `1 trainee is catalogued.` The sentence claims to describe the
 * catalogue, but it is printing the filtered paginator's total, so a Trainer arriving on a filtered
 * URL is told the catalogue is smaller than it is.
 *
 * The failed first attempt at this case is worth recording, because it is the trap: the roster shows
 * six rows a page, so `rows < 66` was true on load as well as after filtering. The guard that was
 * supposed to prove "the filter applied" therefore proved nothing, and the case passed against the
 * unfixed screen. This version waits on the pager's own denominator (`page 1 of N`), which only
 * changes when the filtered result set does, then asserts the lede against it.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 120_000 });

/** The pager's own last-page figure — the filtered set's size divided by the six-row page. */
async function lastPage(page: import('@playwright/test').Page): Promise<number> {
    const line = await page.getByText(/page \d+ of \d+/i).first().innerText();
    const match = /page\s+\d+\s+of\s+(\d+)/i.exec(line);

    expect(match, `the pager line did not state a denominator: ${line}`).not.toBeNull();

    return Number.parseInt(match?.[1] ?? '0', 10);
}

test('the catalogue count states the catalogue, not the filtered result', async ({ page }) => {
    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const lede = page.getByText(/trainees? (is|are) catalogued/i).first();
    await expect(lede).toBeVisible();

    const unfilteredText = await lede.innerText();
    const catalogueSize = Number.parseInt(
        (/([\d,]+)/.exec(unfilteredText)?.[1] ?? '0').replace(/,/g, ''),
        10,
    );

    expect(catalogueSize, 'the unfiltered roster did not state a catalogue size').toBeGreaterThan(10);

    const unfilteredPages = await lastPage(page);

    expect(
        unfilteredPages,
        'the unfiltered roster fits on one page, so this case cannot observe a denominator change',
    ).toBeGreaterThan(1);

    /*
     * Filter to a name that matches a handful of rows. `Agnes` matches more than one trainee on the
     * Global catalogue, which keeps the case honest about a filtered *set* rather than a single hit.
     */
    await page.locator('#filter-search').fill('Agnes');

    // The result set really narrowed: the pager's denominator shrinks. This is the wait, and it is a
    // real signal — the earlier attempt waited on the row count, which page size made always true.
    await expect
        .poll(async () => lastPage(page), { timeout: 15_000 })
        .toBeLessThan(unfilteredPages);

    const filteredText = await lede.innerText();
    const filteredCount = Number.parseInt(
        (/([\d,]+)/.exec(filteredText)?.[1] ?? '0').replace(/,/g, ''),
        10,
    );

    /*
     * The defect. F-02's own wording is that "the count follows the filter and the sentence does not
     * say so" — either half is a fix, and this assertion names the invariant: the sentence says
     * `catalogued`, so the number must be the catalogue's, whatever the filter is doing.
     */
    expect(
        filteredCount,
        `the "catalogued" count followed the filter (${filteredCount} of ${catalogueSize}), so the `
        + 'sentence describes the result set as though it were the catalogue',
    ).toBe(catalogueSize);

    // Clearing restores the opening state, so the fix cannot be "always print the catalogue size by
    // dropping the count from the filtered view entirely".
    await page.getByRole('button', { name: /^Clear/i }).first().click();

    await expect
        .poll(async () => lastPage(page), { timeout: 15_000 })
        .toBe(unfilteredPages);

    await expect(lede).toContainText(String(catalogueSize));
});
