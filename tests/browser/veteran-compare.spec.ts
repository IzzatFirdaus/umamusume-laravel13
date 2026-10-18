import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';

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
 * The six-at-once cap is not repeated in a browser case here on purpose: proving it in the DOM needs seven
 * filed careers, seven create-and-file cycles, and it would assert a rule the request layer already refuses
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

// `fileCareer` lives in `tests/utils/file-career.ts` and is shared with `veterans.spec.ts`; it returns
// the bare run URL, which each call here records so the `afterEach` above can cascade it away.

test('lines careers up with one property per row, reached from the library row', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    createdRunUrls.push(await fileCareer(page, 'Eishin Flash', null));

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
    // actually records. `Note` is deliberately absent from the matrix (Phase 8.10): long-form text moved to
    // a per-career footer card below the table, where it can wrap.
    const rows = await page.getByRole('rowheader').allTextContents();
    expect(rows).toEqual(expect.arrayContaining(['Scenario', 'Turns logged', 'Speed', 'Tags', 'Legacy read-back']));
    expect(rows).not.toContain('Note');
    expect(rows.length).toBeGreaterThan(10);

    // A row that holds a value and a row that holds none are told apart in words, not by blank space.
    await expect(page.getByText('No tags recorded').first()).toBeVisible();

    // The note lives below the table now, one card per selected career.
    await expect(page.getByText('No note recorded for this career.').first()).toBeVisible();

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
    createdRunUrls.push(await fileCareer(page, 'Super Creek', 'Speed'));

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

    // One career fits the region, so the pinned scroll bar is not drawn (Phase 8.20). A second, empty
    // scrollbar is a second lie about the layout; it exists only for the state that overflows.
    await expect(page.locator('.comparison-scroll-mirror')).toHaveCount(0);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Admire Vega', 'Speed'));

    // Scanned with a column rendered rather than on the empty picker: the aligned table is what this screen
    // exists to print, and a career's Japanese name only appears inside its column head.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Compare this career' }).first().click();
    await page.waitForURL(/\/veterans\/compare\?veterans\[\]=\d+/, WRITE);

    const results = await buildAxe(page).analyze();

    expect(results.violations).toEqual([]);
});

/**
 * Files two careers, opens the library's row door on the second, ticks the first and compares, so the
 * matrix renders with two columns. Repeats the flow a Trainer takes rather than a pre-seeded query string,
 * because the door and the picker are both parts of the selection path Phase 8 touches.
 */
async function openTwoCareerComparison(
    page: import('@playwright/test').Page,
    first: string,
    second: string,
    secondTag: string | null = null,
): Promise<void> {
    createdRunUrls.push(await fileCareer(page, first, 'Speed'));
    createdRunUrls.push(await fileCareer(page, second, secondTag));

    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('listitem').filter({ hasText: second })
        .getByRole('link', { name: 'Compare this career' })
        .click();
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);

    await page.getByRole('checkbox', { name: first }).check();
    await page.getByRole('button', { name: 'Compare' }).click();
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);
    await expect(page.getByRole('heading', { name: '2 careers side by side' })).toBeVisible(WRITE);
}

test('pins the property column and the header row so a value never loses its label', async ({ page }) => {
    await openTwoCareerComparison(page, 'Agnes Digital', 'Eishin Flash');

    // The sticky contract, read from the rendered element rather than from the source: a scoped rule that
    // did not reach the cell is exactly the failure this asserts against (Phase 8.9, 8.21).
    const corner = page.locator('thead th').first();
    await expect(corner).toHaveText(/Property/);

    const computed = await corner.evaluate((element) => {
        const style = window.getComputedStyle(element);

        return {
            position: style.position,
            left: style.left,
            top: style.top,
            zIndex: Number(style.zIndex),
            background: style.backgroundColor,
        };
    });

    expect(computed.position, 'the property column scrolls away from its values').toBe('sticky');
    expect(computed.left).toBe('0px');
    expect(computed.zIndex).toBeGreaterThan(10);
    // Opaque, not transparent: a see-through sticky cell lets the data scroll behind its own label.
    expect(computed.background, 'the sticky column is transparent').not.toBe('rgba(0, 0, 0, 0)');

    const careerHead = page.locator('thead th').nth(1);
    const headerComputed = await careerHead.evaluate((element) => {
        const style = window.getComputedStyle(element);

        return { position: style.position, top: style.top, background: style.backgroundColor };
    });

    expect(headerComputed.position, 'the header row scrolls out of view').toBe('sticky');
    expect(headerComputed.top).toBe('0px');
    expect(headerComputed.background, 'the sticky header is transparent').not.toBe('rgba(0, 0, 0, 0)');
});

test('counts the selection, clears it and says why nothing can compare', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Super Creek', 'Speed'));

    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const count = page.getByText(/of \d+ selected/);
    await expect(count).toContainText('0 of');

    // The empty picker states the reason rather than leaving a dead button to be clicked first.
    await expect(page.getByText('Select at least one career to compare.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Compare' })).toBeDisabled();

    await page.getByRole('checkbox', { name: 'Super Creek' }).check();
    await expect(count).toContainText('1 of');
    await expect(page.getByText('Select at least one career to compare.')).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Compare' })).toBeEnabled();

    await page.getByRole('button', { name: 'Clear all' }).click();
    await expect(count).toContainText('0 of');
    await expect(page.getByRole('button', { name: 'Compare' })).toBeDisabled();
});

test('keeps the columns in the order the Trainer ticked them', async ({ page }) => {
    // Filed first, so the server's ordering puts it first; ticked second, so the column order must not
    // follow the server. This is the difference between a comparison of the two things the Trainer asked
    // for and a comparison of two things that happen to be adjacent in the table (Phase 8.17).
    createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    createdRunUrls.push(await fileCareer(page, 'Eishin Flash', null));

    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('listitem').filter({ hasText: 'Agnes Digital' })
        .getByRole('link', { name: 'Compare this career' })
        .click();
    await page.waitForURL(/\/veterans\/compare\?veterans\[\]=\d+/, WRITE);

    await page.getByRole('checkbox', { name: 'Eishin Flash' }).check();
    await page.getByRole('button', { name: 'Compare' }).click();
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);
    await expect(page.getByRole('heading', { name: '2 careers side by side' })).toBeVisible(WRITE);

    const headings = page.getByRole('columnheader');
    await expect(headings.nth(1)).toContainText('Agnes Digital');
    await expect(headings.nth(2)).toContainText('Eishin Flash');
});

test('says so when two careers share every value instead of repeating them silently', async ({ page }) => {
    // The same scenario, the same tag and no logged turns on either, so every recorded value agrees and
    // only the identity differs. The matrix still renders (the Trainer came to read it) and a note above
    // it says the table has nothing to show them (Phase 8.15).
    await openTwoCareerComparison(page, 'Agnes Digital', 'Eishin Flash', 'Speed');

    await expect(
        page.getByText('All selected careers share every value; nothing differs.'),
    ).toBeVisible();
    await expect(page.getByRole('rowheader', { name: 'Scenario' })).toBeVisible();
});

test('shows one career at a time under lg rather than scrolling the matrix sideways', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await openTwoCareerComparison(page, 'Agnes Digital', 'Eishin Flash');

    // A pager, so the labels the reader is following and the value under it never part company by the
    // width of a career column (Phase 8.19; Law 11 Continuity).
    const next = page.getByRole('button', { name: 'Next career' });
    await expect(next).toBeVisible();

    const visibleHeaders = await page.locator('thead th').evaluateAll((cells) =>
        cells.filter((cell) => cell.getClientRects().length > 0).length,
    );
    expect(visibleHeaders, 'more than the property column and one career show at 390px').toBe(2);

    // The document itself never scrolls sideways at phone width (WCAG 1.4.10).
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the document scrolls horizontally at 390px').toBeLessThanOrEqual(1);

    // Paging moves which career fills the second column rather than adding a third: exactly one career
    // header answers at phone width, and it is one of the two the Trainer selected.
    const names = page.getByRole('columnheader').filter({ hasText: /Eishin Flash|Agnes Digital/ });
    await expect(names).toHaveCount(1);
    await expect(page.getByText(/1 of 2\)/)).toBeVisible();

    await next.click();
    await expect(page.getByText(/2 of 2\)/)).toBeVisible();

    // The pager is a narrow-viewport measure; the whole matrix is back at 1280.
    await page.setViewportSize({ width: 1280, height: 900 });
    await expect(page.getByRole('button', { name: 'Next career' })).toHaveCount(0);

    const wideHeaders = await page.locator('thead th').evaluateAll((cells) =>
        cells.filter((cell) => cell.getClientRects().length > 0).length,
    );
    expect(wideHeaders, 'the full matrix does not render at 1280').toBe(3);
});

test('pins a horizontal scrollbar that stays reachable when the matrix overflows', async ({ page }) => {
    // Six careers is the point of the exercise: at six columns the matrix is wider than a 1024 viewport,
    // and that is the only state in which the scroll affordance exists. It also proves the six-career
    // cap in the DOM, which the request layer owns on its own (Phase 8.11).
    test.setTimeout(300_000);

    for (let index = 0; index < 6; index += 1) {
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    }

    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const picks = page.locator('input[type="checkbox"]');
    for (let index = 0; index < 6; index += 1) {
        await picks.nth(index).check();
    }

    await page.getByRole('button', { name: 'Compare' }).click();
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);
    await expect(page.getByRole('heading', { name: '6 careers side by side' })).toBeVisible(WRITE);

    const region = page.getByRole('region', { name: 'Career comparison, scrollable' });
    const mirror = page.locator('.comparison-scroll-mirror');

    // Six career columns do not fit the content column at any supported width, so the bar belongs here.
    // It is absent for the one-career fixture above, which is what makes its presence here a fact about
    // the layout rather than a decoration (Phase 8.20).
    await page.setViewportSize({ width: 1024, height: 900 });
    await expect(mirror).toBeVisible(WRITE);

    // Pinned, so the bar is reachable from any part of the table rather than only from its end.
    const bar = await mirror.boundingBox();
    expect(bar).not.toBeNull();
    expect(bar?.y ?? 0, 'the scroll bar is not in the first viewport').toBeGreaterThan(0);
    expect((bar?.y ?? 0) + (bar?.height ?? 0), 'the scroll bar sits below the fold').toBeLessThanOrEqual(900);

    // Either bar drives the view: moving the mirror moves the region, so a reader who drags the pinned
    // bar gets the same matrix they would have got from the native one.
    await mirror.evaluate((element) => {
        element.scrollLeft = 60;
    });
    await page.waitForTimeout(300);
    expect(await region.evaluate((element) => element.scrollLeft), 'the mirror does not drive the table').toBeGreaterThan(0);

    // And the region contract is unchanged by the wrapper the mirror introduced.
    await expect(region).toHaveAttribute('tabindex', '0');
    await expect(region).toHaveAttribute('role', 'region');
});

// Plan slice 0.4. Two careers of one trainee filed in different scenarios must produce column
// headers a Trainer can tell apart. Both rows are filed in the same second by this spec, so the
// scenario is the part of the label carrying the difference; the date alone would not separate them.
// Data is created here rather than assumed from whatever the seeded library holds.
test('gives two careers of one trainee different column headers', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Agnes Digital', null, null, 'ura_finale'));
    const secondRunUrl = await fileCareer(page, 'Agnes Digital', null, null, 'unity_cup');
    createdRunUrls.push(secondRunUrl);

    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('listitem').filter({ hasText: 'URA Finale' })
        .getByRole('link', { name: 'Compare this career' })
        .click();
    await page.waitForURL(/\/veterans\/compare\?veterans\[\]=\d+/, WRITE);

    // The second pick is addressed by its own label, not by the trainee name: both rows carry the
    // same name now, and a name query would be ambiguous.
    await page.getByRole('checkbox', { name: /Unity Cup/ }).check();
    await page.getByRole('button', { name: 'Compare' }).click();
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);

    await expect(page.getByRole('heading', { name: '2 careers side by side' })).toBeVisible();

    // The link text inside each `th`, not the `th`'s whole text: the header also carries the
    // Japanese name and the scenario as sibling spans, so a whole-cell comparison differs even when
    // the career name is identical. Asserting the label means the assertion fails if the label is
    // what regressed, which is the defect this case exists for.
    const labels = await page
        .getByRole('columnheader')
        .locator('a')
        .allTextContents()
        .then((all) => all.map((t) => t.trim()).filter((t) => t.includes('Agnes Digital')));

    expect(new Set(labels).size, `column labels must differ: ${JSON.stringify(labels)}`).toBe(2);
    expect(labels.some((l) => l.includes('URA Finale'))).toBe(true);
    expect(labels.some((l) => l.includes('Unity Cup'))).toBe(true);
});