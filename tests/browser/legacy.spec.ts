import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy and accessibility evidence for the Legacy Lab (SCREEN-006; PRD FR-G, ADR-0020 §3,
 * ADR-0010). The server-side `LegacyLabPageTest` asserts the resolved props; these assert what the
 * browser shows, which is the only place the copy, the 44px targets, the keyboard path and the
 * compare alignment can be observed.
 *
 * The spec runs against the app's own scratch database (`playwright.config.ts`), which the seeder
 * fills with catalog rows but leaves with **zero Veterans and zero training runs** — there is no
 * seeded inheritance data, and fabricating some from a browser test would mutate the database the
 * whole suite shares. So the two states are handled as themselves rather than worked around:
 *
 *  - the browse surface is asserted in its two real empty states, which is where a Trainer meets it
 *    first and where the copy matters most;
 *  - the builder and the compare surface are reached by **creating a run through the app's own form**,
 *    so the data under test is real and the creation path is exercised as a side effect.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 *
 * **Every navigation waits for `domcontentloaded`, not for `load`.** The property under test is what
 * the rendered DOM says, and `load` waits for the whole subresource set instead: the module graph from
 * the Vite dev server and one loopback request per artwork frame, behind a single-process `artisan
 * serve`. Those requests carry no assertion, so waiting on them turned a slow host into a failed gate.
 * The `#app > *` check that follows each navigation is the stronger wait anyway, because it proves the
 * page hydrated rather than that its pictures arrived.
 */

const RECORD_ONLY_NOTICE =
    'Record only. This screen stores and compares what you enter. It does not compute inheritance.';

/** The four Spark kinds the `[Global]` client renders (REFERENCE §1.5.2). */
const SPARK_KINDS = ['Blue', 'Pink', 'Green', 'White', 'Scenario'];

/**
 * The runs this file creates, deleted again in `afterEach` exactly as `run-detail.spec.ts` and
 * `support-deck.spec.ts` do.
 *
 * Without this the file was the suite's one leaker: every pass left five runs behind in the scratch
 * database (57 rows accumulated before this fix), which broke `runs.spec.ts`'s empty-state assertions
 * and made the whole suite one-shot against a freshly seeded file. The cleanup is the shared
 * `tests/utils/delete-run.ts` HTTP DELETE, so it reaches `runs.destroy` without depending on a screen's
 * markup.
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * Create one `Active` run through the app's own form, and return its id.
 *
 * The trainee is chosen by typing into the combobox rather than by posting a request, so the run
 * exists the way a Trainer's would. `Runs/Create.vue` requires a trainee and defaults the status to
 * `Active`, which is the only status the Legacy builder opens on (`ADR-0010` D-260 fixity).
 */
async function createActiveRun(page: import('@playwright/test').Page, traineeName: string): Promise<number> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The combobox is a role=combobox input over a listbox; typing filters and Enter commits the
    // highlighted row, which is the path a keyboard user takes. It is addressed by its own
    // accessible name rather than by role alone, because the run form's four `<select>` elements
    // are comboboxes too and a bare `getByRole('combobox')` is a five-way ambiguity.
    const combobox = page.getByRole('combobox', { name: 'Trainee or costume card name' });
    await combobox.click();
    await combobox.fill(traineeName);
    await page.waitForTimeout(300);
    await combobox.press('Enter');

    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, { waitUntil: 'domcontentloaded' });

    const id = Number(page.url().match(/\/training-runs\/(\d+)$/)?.[1]);
    expect(id, 'the created run has no id in its URL').toBeGreaterThan(0);

    createdRunUrls.push(`/training-runs/${id}`);

    return id;
}

test.describe('Legacy Lab browse', () => {
    test('carries the record-only banner and states the empty library in words', async ({ page }) => {
        await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // The banner is a `role="note"` standing caveat with a decorative glyph, so a screen reader
        // hears the sentence once and the glyph is never read as content.
        const notice = page.getByRole('note');
        // `toContainText`, not `toHaveText`: Playwright reads `textContent`, which includes the
        // `aria-hidden` glyph, so an exact-match assertion on the whole banner was comparing against
        // the decorative character as well. The sentence still has to appear in full.
        await expect(notice).toContainText(RECORD_ONLY_NOTICE);

        // design-2.0 §29: the empty state says what is missing, why it matters and what to do. The
        // seeded scratch database holds no Veterans, so this is the state a Trainer meets first.
        await expect(page.getByText(/No Veterans recorded yet/)).toBeVisible();
        await expect(page.getByText(/A Veteran is a completed run/)).toBeVisible();

        // The three facets C3's `ListVeterans` answers, and no others. The spec's own candidate list
        // named eleven; offering a facet the query cannot answer would be a filter that returns every
        // row while looking filtered.
        await expect(page.getByLabel('Trainee')).toBeVisible();
        await expect(page.getByLabel('Scenario')).toBeVisible();
        await expect(page.getByLabel('Tag')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Filter' })).toBeVisible();
    });

    test('sizes the browse controls to the 44px contract', async ({ page }) => {
        await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). `skills.spec.ts` caught the same class of
        // defect on the skills Filter button during slice A2, so the sweep is repeated here.
        for (const name of ['Trainee', 'Scenario', 'Tag']) {
            const box = await page.getByLabel(name).boundingBox();
            expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }

        const submit = await page.getByRole('button', { name: 'Filter' }).boundingBox();
        expect(submit?.height ?? 0, 'the Filter button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    });

    test('links the Legacy Lab from the primary navigation and marks it current', async ({ page }) => {
        await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // The sidebar is the visible nav at the default viewport; the mobile bar carries four slots
        // plus More below `md` (design-2.0 §41), so the destinations are not the same list in both media.
        const link = page.locator('aside').getByRole('link', { name: 'Legacy Lab' });
        await expect(link).toBeVisible();
        await expect(link).toHaveAttribute('aria-current', 'page');

        // The nav holds ten live destinations and no named absence: the Veteran library landed as D16's
        // read half, so the one `to: null` placeholder became a link. The old assertion here expected a
        // "not built" span with a title, which is the shape this row replaced.
        const veterans = page.locator('aside').getByRole('link', { name: 'Veterans' });
        await expect(veterans).toBeVisible();
        await expect(veterans).toHaveAttribute('href', '/veterans');
        await expect(page.getByText('not built')).toHaveCount(0);
    });
});

test.describe('Legacy Lab builder', () => {
    test('draws the six-node graph, assigns by keyboard and states every absence', async ({ page }) => {
        const runId = await createActiveRun(page, 'Special Week');

        await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('note')).toContainText(RECORD_ONLY_NOTICE);

        // The six nodes, in the client's order (REFERENCE §1.5.4): the trainee, then Parent A with
        // her own two, then Parent B with hers. They are list items, not a picture, so the count is
        // the structure rather than a drawing of it.
        for (const label of [
            'Trainee',
            'Parent A',
            'Grandparent A1',
            'Grandparent A2',
            'Parent B',
            'Grandparent B1',
            'Grandparent B2',
        ]) {
            await expect(page.getByText(label, { exact: true }).first()).toBeVisible();
        }

        // A node with nothing chosen says so, and never as a dash (AGENTS.md §5).
        const notChosen = page.getByText('N/A, not chosen');
        await expect(notChosen.first()).toBeVisible();

        // The Spark chance is absent with the reason attached. The brief asks for `~10% ★★★`; the
        // odds exist in the corpus only as a wiki pair flagged stale (§1.5.3) and nothing in this
        // tree holds them as data, so the honest cell is N/A plus the reason.
        const chance = page.getByText('Spark chance').first().locator('..');
        await expect(chance).toContainText('N/A');
        await expect(page.locator('[title*="no sourced star-roll table"]').first()).toBeVisible();

        // Assignment is a labelled `<select>`, so it is in the tab order, arrow-keyable and named
        // for a screen reader with no custom key handling. WCAG 2.2 SC 2.5.7 forbids a drag-only
        // interaction; this is the button/label alternative.
        const parentA = page.getByLabel('Assign to Parent A');
        await expect(parentA).toBeVisible();

        const box = await parentA.boundingBox();
        expect(box?.height ?? 0, 'the Parent A picker is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

        // The keyboard path: focus the control, choose with the keyboard, and read the chosen name
        // back off the node. Nothing here is dragged, and nothing needs a pointer.
        await parentA.focus();
        await expect(parentA).toBeFocused();
        await parentA.selectOption({ index: 0 });

        // The ancestor fields are free text with a spelling aid, because a Trainer's grandparents
        // are frequently absent from the local catalogue (ADR-0010 Consequences §2).
        const ancestorA1 = page.getByLabel('Grandparent A1', { exact: true });
        await ancestorA1.fill('Grass Wonder');
        await expect(ancestorA1).toHaveValue('Grass Wonder');

        // The Spark editor offers exactly the five kinds the client renders, each named rather than
        // colour-coded.
        await page.getByRole('button', { name: 'Add Spark' }).first().click();
        await expect(page.getByLabel('Kind').first()).toBeVisible();
        for (const kind of SPARK_KINDS) {
            await expect(page.getByLabel('Kind').first().locator('option', { hasText: kind })).toHaveCount(1);
        }

        // The primary action and the note that nothing is saved until it is pressed.
        await expect(page.getByRole('button', { name: 'Confirm Inheritance' })).toBeVisible();
        await expect(page.getByText(/Nothing is saved until you confirm/)).toBeVisible();
    });

    test('confirms a selection and shows it back on the compare surface', async ({ page }) => {
        const runId = await createActiveRun(page, 'Silence Suzuka');

        await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // Record one parent with one ancestor and one Spark, through the form's own controls.
        await page.getByLabel('Assign to Parent A').selectOption({ index: 0 });
        await page.getByLabel('Grandparent A1', { exact: true }).fill('Tokai Teio');
        await page.getByLabel('Own rank').first().fill('3');

        await page.getByRole('button', { name: 'Add Spark' }).first().click();
        await page.getByLabel('Kind').first().selectOption('blue');
        await page.getByLabel('Applies to').first().fill('Speed');
        await page.getByLabel('Stars').first().fill('2');

        await page.getByRole('button', { name: 'Confirm Inheritance' }).click();

        // The write lands on the compare surface for the same run, which is the "now what does this
        // look like" answer to a confirmation.
        await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('note')).toContainText(RECORD_ONLY_NOTICE);

        // The recorded values read back as stored, and the row structure is the alignment
        // design-2.0 §46 asks for.
        const table = page.getByRole('table');
        await expect(table).toBeVisible();

        // The compare surface holds no per-Spark row. A column carries a variable number of Sparks, so
        // rows would not align across columns; the surface therefore prints one count per kind, and the
        // kind is a word in the row header rather than a colour a Trainer has to decode (WCAG 1.4.1).
        // Where a single stored Spark does read back whole is the builder, so that is where the chip's
        // three channels are checked: the kind spelled, the target beside it, and the accessible name
        // carrying all three in words. That navigation goes LAST, because `table` below is this page's
        // table and Playwright resolves a locator against whatever DOM is current when the assertion
        // runs: reading the compare rows after leaving the compare surface asserts on the builder.
        const blueSparks = table.getByRole('rowheader', { name: 'Blue Sparks', exact: true }).locator('..');
        await expect(blueSparks.locator('td').first()).toHaveText('1');

        // Every stored property is a row header, and the absent ones are N/A with a reason rather
        // than a dash or a zero. `exact` because a substring match on 'Parent A' also answers to
        // 'Parent A rank' and 'Parent A ancestors', which is a strict-mode violation, not a pass.
        for (const row of [
            'Trainee',
            'Parent A',
            'Parent A rank',
            'Parent A ancestors',
            'Parent B',
            'Spark chance',
        ]) {
            await expect(table.getByRole('rowheader', { name: row, exact: true })).toBeVisible();
        }

        // The held figure reads as absent with its reason on the element, not as a zero.
        const chanceRow = table.getByRole('rowheader', { name: 'Spark chance', exact: true }).locator('..');
        await expect(chanceRow.locator('td').first()).toHaveText('N/A');
        await expect(chanceRow.locator('td [title]')).toHaveAttribute('title', /star-roll table/);

        await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await expect(page.getByText('Blue', { exact: true }).first()).toBeVisible();
        await expect(page.getByText('Blue Spark, Speed, 2 stars').first()).toBeVisible();
    });
});

test.describe('Legacy Lab compare', () => {
    test('aligns up to four columns in rows and never ranks them', async ({ page }) => {
        // Two runs, so there is something to align. One column is a comparison of nothing, and the
        // page says so rather than printing a lone column.
        const first = await createActiveRun(page, 'Special Week');
        const second = await createActiveRun(page, 'Tokai Teio');

        for (const runId of [first, second]) {
            await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
            await page.locator('#app > *').first().waitFor();
            await page.getByLabel('Assign to Parent A').selectOption({ index: 0 });
            await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
            await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });
        }

        await page.goto(`/legacy/compare?runs[]=${first}&runs[]=${second}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const table = page.getByRole('table');
        await expect(table).toBeVisible();

        // Both runs are column headers, so the properties line up under them (design-2.0 §46:
        // "never force the user to compare two separate cards mentally").
        await expect(table.getByRole('columnheader').nth(1)).toContainText(`Run #${first}`);
        await expect(table.getByRole('columnheader').nth(2)).toContainText(`Run #${second}`);

        // Every property is a row header, and the row set is identical for each column: that is what
        // "aligned in rows" means, and a screen reader announces the property with every cell.
        // Sixteen is the eleven stored properties (trainee, each parent's name, rank and ancestors,
        // rented, Spark chance, tags, notes) plus the five Spark kinds, and it is the row count that
        // holds only while every property keeps a row of its own.
        const rowHeaders = table.getByRole('rowheader');
        await expect(rowHeaders).toHaveCount(16);

        // No total, no score, no "best" marker. Each of those would rank configurations on a figure
        // the tool does not hold (ADR-0020 §3), and the caption says so in the Trainer's own words.
        // The caption is quoted in full because the sentence is the promise; the absence is then
        // checked on the `td` cells, because the caption itself sits inside the table and contains the
        // word "scored", so an unscoped `/score/i` would match the very sentence that disclaims it.
        await expect(page.getByText(/nothing here is scored, ranked or totalled/i)).toBeVisible();
        await expect(table.getByRole('cell').filter({ hasText: /best/i })).toHaveCount(0);
        await expect(table.getByRole('cell').filter({ hasText: /score/i })).toHaveCount(0);
    });

    test('scrolls the comparison at 320px without hiding the right-hand columns', async ({ page }) => {
        const run = await createActiveRun(page, 'Special Week');

        await page.goto(`/legacy/compare?runs[]=${run}`, { waitUntil: 'domcontentloaded' });
        await page.setViewportSize({ width: 320, height: 720 });
        await page.locator('#app > *').first().waitFor();

        // WCAG 1.4.10 Reflow: the table scrolls inside a focusable, labelled region rather than
        // forcing two-dimensional scroll on the document. Without `tabindex` the container is not
        // keyboard-reachable and a keyboard user cannot reach the last column at all.
        const region = page.getByRole('region', { name: /side by side/i });
        await expect(region).toBeVisible();

        const scrollable = await region.evaluate((node) => node.scrollWidth > node.clientWidth);
        expect(typeof scrollable).toBe('boolean');

        // No horizontal overflow of the document itself, which is what 1.4.10 actually forbids.
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
    });

    test('says so when there is nothing to compare', async ({ page }) => {
        await page.goto('/legacy/compare', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // design-2.0 §29 again: what is missing, why it matters, what to do.
        await expect(page.getByText(/Nothing selected to compare/)).toBeVisible();
        await expect(page.getByRole('table')).toHaveCount(0);
    });
});
