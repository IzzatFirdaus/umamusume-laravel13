import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { PAGE_MARKERS, assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';

const WIREFRAME = pathToFileURL(
    resolve('docs/design-research/frontend-v2-wireframes/veterans-compare.html'),
).href;

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

/**
 * Opens the comparison for the named careers the way a Trainer now does it: tick the rows on the
 * library, then take the head band's door. The picker used to live on the compare page, so every case
 * here entered through a row's "Compare this career" link and ticked the second career on the page it
 * landed on. That page has no selection controls anymore, which is the point of the move: choosing and
 * reading were the same screen, and the screen could not be linked to without shipping a form.
 *
 * The order the names are given is the order they are ticked, and the compare page reads that order
 * back out of the query string, so a case that cares about column order gets it from here.
 */
async function openComparison(page: import('@playwright/test').Page, trainees: string[]): Promise<void> {
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    for (const trainee of trainees) {
        // A substring, not the whole name: the checkbox's accessible name carries the row's full label
        // (`Agnes Digital · URA Finale · 2026-11-01`) so two careers of one trainee stay addressable.
        await page.getByRole('checkbox', { name: trainee }).check();
    }

    await page.getByRole('link', { name: `Compare ${trainees.length} selected` }).click();
    // The path, not the query spelling. The href is written `?veterans[]=N`, but Inertia's Link
    // re-serialises the array as `?veterans%5B0%5D=N&veterans%5B1%5D=M`, and PHP builds the same list
    // from either spelling. Matching the query shape is how this helper broke once already.
    await page.waitForURL(/\/veterans\/compare\?/, WRITE);
    await page.locator('#app > *').first().waitFor();
}

test('lines careers up with one property per row, reached from the library row', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    createdRunUrls.push(await fileCareer(page, 'Eishin Flash', null));

    await openComparison(page, ['Eishin Flash', 'Agnes Digital']);

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
    // UX-19: the list moved from an always-open section into a collapsed disclosure, so the first
    // viewport belongs to the comparison. Collapsed on load; openable in one click.
    const absences = page.locator('details').filter({ has: page.locator('summary', { hasText: 'NOT IN THIS BUILD' }) });
    await expect(absences).not.toHaveAttribute('open', '');
    await absences.locator('summary').click();
    await expect(page.getByText(/Inheritance usefulness and a compatibility calculation/)).toBeVisible();
});

test('says nothing is selected before anything is picked, rather than showing an empty table', async ({ page }) => {
    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Nothing selected' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Open the library' })).toBeVisible();

    // The empty state now names the library as the place careers are ticked. The inert Compare button
    // this line asserted was the picker's submit control, and the picker moved: on the library the
    // door is a link that counts the selection instead of a button that could be disabled.
    await expect(
        page.getByText(/Tick up to \d+ careers on the library and open the comparison from there/),
    ).toBeVisible();
});

test('sizes the comparison controls to the 44px contract', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Super Creek', 'Speed'));

    // The picker's tap target is measured where the picker lives now. It is the label row that carries
    // the input, not the native 24px box inside it: the row is `min-h-11` and clicking anywhere on it
    // toggles the pick.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const libraryTargets = [
        page.locator('label').filter({ has: page.getByRole('checkbox', { name: 'Super Creek' }) }),
        page.getByRole('link', { name: 'Compare veterans' }),
    ];

    for (const target of libraryTargets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a library selection control is below the 44px floor').toBeGreaterThanOrEqual(44);
    }

    await page.goto('/veterans/compare', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const targets = [
        page.getByRole('link', { name: 'Back to library' }),
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

    // Scanned with a column rendered rather than on an empty comparison: the aligned table is what this
    // screen exists to print, and a career's Japanese name only appears inside its column head.
    await openComparison(page, ['Admire Vega']);

    const results = await buildAxe(page).analyze();

    expect(results.violations).toEqual([]);
});

/**
 * Files two careers and opens the comparison with the second ticked first, so the matrix renders with
 * two columns in a known order. It repeats the flow a Trainer takes rather than pre-seeding a query
 * string, because the checkbox and the counting door are both parts of the selection path.
 */
async function openTwoCareerComparison(
    page: import('@playwright/test').Page,
    first: string,
    second: string,
    secondTag: string | null = null,
): Promise<void> {
    createdRunUrls.push(await fileCareer(page, first, 'Speed'));
    createdRunUrls.push(await fileCareer(page, second, secondTag));

    // Ticked in this order on purpose: the compare page reads column order from the query string, so
    // `second` leads and the server's own filing order (which puts `first` ahead) must not win.
    await openComparison(page, [second, first]);
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

test('counts the selection on the library door and clears it when the tick comes back off', async ({ page }) => {
    createdRunUrls.push(await fileCareer(page, 'Super Creek', 'Speed'));

    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The door is the count. The picker used to carry its own "0 of 6 selected" status beside a button
    // that disabled itself; on the library the head band's one accented control says the number
    // instead, so there is no second readout to fall out of step with it.
    const door = page.getByRole('link', { name: 'Compare veterans' });
    await expect(door).toBeVisible();

    await page.getByRole('checkbox', { name: 'Select Super Creek' }).check();
    await expect(page.getByRole('link', { name: 'Compare 1 selected' })).toBeVisible();
    await expect(door).toHaveCount(0);

    await page.getByRole('checkbox', { name: 'Select Super Creek' }).uncheck();
    await expect(door).toBeVisible();
    await expect(page.getByRole('link', { name: 'Compare 1 selected' })).toHaveCount(0);

    // "why nothing can compare" moved with the selection. An empty door still leads somewhere honest:
    // the compare page states the reason in its own empty state, which the case above asserts.
});

test('keeps the columns in the order the Trainer ticked them', async ({ page }) => {
    // Filed first, so the server's ordering puts it first; ticked second, so the column order must not
    // follow the server. This is the difference between a comparison of the two things the Trainer asked
    // for and a comparison of two things that happen to be adjacent in the table (Phase 8.17).
    createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    createdRunUrls.push(await fileCareer(page, 'Eishin Flash', null));

    await openComparison(page, ['Agnes Digital', 'Eishin Flash']);

    const headings = page.getByRole('columnheader');
    await expect(headings.nth(1)).toContainText('Agnes Digital');
    await expect(headings.nth(2)).toContainText('Eishin Flash');
});

test('says so when two careers share every value instead of repeating them silently', async ({ page }) => {
    // The same scenario, the same tag and no logged turns on either, so every recorded value agrees and
    // only the identity differs. The matrix still renders (the Trainer came to read it) and a note above
    // it says the table has nothing to show them (Phase 8.15).
    await openTwoCareerComparison(page, 'Agnes Digital', 'Eishin Flash', 'Speed');

    // The shipped sentence, not an earlier draft of it: the note is the HFW's "Nothing decisive"
    // verdict ("a comparison that can only produce winners is a recommendation engine").
    await expect(
        page.getByText('Nothing decisive: all selected careers share every value.'),
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

    // The Difference column hides with the career columns it summarises (`hidden lg:table-cell`): a
    // verdict over values paged out of view would be a comparison the reader cannot check. It is in
    // the DOM, so the fold is real at every width; only its visibility follows the width.
    //
    // Asserted with a locator, not a role: `getByRole` resolves through the accessibility tree, and a
    // `display: none` cell is not in that tree at all, so a role query can never count the hidden
    // header no matter what the DOM holds. The two assertions below are the two claims this line owns.
    const differenceHeader = page.locator('thead th').filter({ hasText: 'Difference' });
    await expect(differenceHeader).toHaveCount(1);
    await expect(differenceHeader).not.toBeVisible();

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
    // Property + two careers + the Difference column: the verdict joined the matrix, so the full
    // matrix is one column wider than it was.
    expect(wideHeaders, 'the full matrix does not render at 1280').toBe(4);
});

test('pins a horizontal scrollbar that stays reachable when the matrix overflows', async ({ page }) => {
    // Six careers is the point of the exercise: at six columns the matrix is wider than a 1024 viewport,
    // and that is the only state in which the scroll affordance exists. It also proves the six-career
    // cap in the DOM, which the request layer owns on its own (Phase 8.11).
    test.setTimeout(300_000);

    for (let index = 0; index < 6; index += 1) {
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));
    }

    // Six careers of one trainee, so the boxes are addressed by position rather than by name: every
    // row carries the same label, and a name query would match all six.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const picks = page.locator('input[type="checkbox"]');
    for (let index = 0; index < 6; index += 1) {
        await picks.nth(index).check();
    }

    await page.getByRole('link', { name: 'Compare 6 selected' }).click();
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

    // Each pick is addressed by the scenario inside its own label, not by the trainee name: both rows
    // carry the same name now, and a bare name query would be ambiguous.
    await openComparison(page, ['URA Finale', 'Unity Cup']);

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

/*
 * Structural parity against the high-fidelity wireframe, the same instrument `veterans-parity.spec.ts`
 * runs for the library. `regions: 'subset'` asks only that every marker the wireframe draws exists on the
 * page, in the wireframe's order; the app's extra markers (the picker, the absence register, the per-career
 * note cards) are surplus the ruling allows rather than failures. The wireframe's `.keybox` annotation key
 * is not in `PAGE_MARKERS`, so it is ignored rather than demanded.
 */
test('renders the wireframe regions in the wireframe order at 1280', async ({ page }) => {
    for (const trainee of ['Agnes Digital', 'Eishin Flash', 'Gold Ship'] as const) {
        createdRunUrls.push(await fileCareer(page, trainee, null));
    }

    await page.setViewportSize({ width: 1280, height: 900 });
    await openComparison(page, ['Agnes Digital', 'Eishin Flash', 'Gold Ship']);

    // Whole-page scope, and it only became possible with this slice: the picker used to sit on this
    // page and own the first `grid`, so the comparison had to be narrowed to the comparison subtree to
    // avoid measuring the picker's two-column checkbox grid against the wireframe's three-card band.
    // With the selection back on the library, `main` is the honest root and the grid signature is
    // worth asserting again.
    const app = await capture(page, 'main', { markers: PAGE_MARKERS });

    expect(app.regions.length, 'the comparison rendered, not its empty state').toBeGreaterThan(0);

    await page.goto(WIREFRAME);
    const hfw = await capture(page, '.canvas', { markers: PAGE_MARKERS });

    assertStructurallyEqual(app, hfw, { regions: 'subset', grid: true });
});