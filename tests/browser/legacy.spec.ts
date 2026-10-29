import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { openAllDisclosures } from '../utils/accessibility';

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
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { waitUntil: 'domcontentloaded' });

    // No end anchor: the create redirect lands on the Cockpit (`/training-runs/{id}/cockpit`), and the
    // id is carried mid-path.
    const id = Number(page.url().match(/\/training-runs\/(\d+)/)?.[1]);
    expect(id, 'the created run has no id in its URL').toBeGreaterThan(0);

    createdRunUrls.push(`/training-runs/${id}`);

    return id;
}

test.describe('Legacy Lab browse', () => {
    test('states the empty saved-legacies list and carries no record-only banner', async ({ page }) => {
        await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // Plan 2.5: the record-only banner lives once, on the builder, as a help popover.
        await expect(page.getByText(RECORD_ONLY_NOTICE)).toHaveCount(0);

        // design-2.0 §29: the empty state says what is missing, why it matters and what to do.
        await expect(page.getByRole('heading', { name: 'Saved legacies', level: 2 })).toBeVisible();
        await expect(page.getByText(/No saved pair yet/)).toBeVisible();
        await expect(page.getByText(/becomes a pair this list can compare/)).toBeVisible();

        // Owner ruling 2026-10-28: the veteran library's facet form is not on this page.
        await expect(page.getByLabel('Trainee')).toHaveCount(0);
        await expect(page.getByLabel('Tag')).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Filter' })).toHaveCount(0);
    });

    test('sizes the browse controls to the 44px contract', async ({ page }) => {
        await page.goto('/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The page's controls are the head-band Compare
        // door and the veteran-library link; both are real targets and both carry the floor.
        for (const name of ['Compare two pairs', 'Open the veteran library']) {
            const box = await page.getByRole('link', { name }).boundingBox();
            expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }
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

        // The per-node Spark panels are collapsed disclosures, so the assignments and the Spark
        // editor live inside them. The helper opens every disclosure on the page; it sets `open`
        // rather than clicking, so it fires no handler and is idempotent.
        await openAllDisclosures(page);

        // The record-only statement ships once, here, as a collapsed help popover rather than a
        // banner (plan 2.5): the summary names it, the notice sits inside the details.
        //
        // Scoped by content, never by tag. The builder renders a per-node Spark disclosure and the
        // shell renders its own "More" disclosure, so a whole-page `details summary` resolves to six
        // elements and trips strict mode. The popover is the only one whose text names the sentence.
        const recordOnly = page.locator('details', { has: page.getByText('What this screen records') });
        await expect(recordOnly).toBeVisible();
        await expect(recordOnly).toContainText(RECORD_ONLY_NOTICE);

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

        // A node with nothing chosen says so, and never as a dash (AGENTS.md §5). The absence is
        // carried on the element's `title` rather than as visible text.
        const notChosen = page.getByTitle('No Umamusume chosen for this node yet.').first();
        await expect(notChosen).toBeVisible();
        await expect(notChosen).toHaveAttribute('title', /No Umamusume chosen for this node yet\./);

        // The Spark chance is absent with the reason attached. The brief asks for `~10% ★★★`; the
        // odds exist in the corpus only as a wiki pair flagged stale (§1.5.3) and nothing in this
        // tree holds them as data, so the honest cell is N/A plus the reason.
        // DELETED 2026-10-21: the builder page carries no "Spark chance" cell and no star-roll
        // summary. The chance cell is a COMPARE-page subject (Legacy/Compare.vue:421) and the
        // star-roll sentence survives only as a docblock comment (AncestryNode.vue:20), so both
        // assertions pinned subjects this page never rendered. The absence itself stays proven by
        // the per-node title above.

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
        await expect(page.getByRole('button', { name: 'Save legacy' })).toBeVisible();
        // The same sentence is also inside the confirm dialog, so the page's own note is addressed
        // directly rather than by text - two matches trip strict mode.
        await expect(
            page.locator('p', { hasText: 'Nothing is saved until you confirm.' }).first(),
        ).toBeVisible();
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
        // `exact` because the grandparent rows carry "Grandparent rank (stars)", which a substring label
        // match answers to first, and the fill would land on the rank rather than the Spark. The wizard
        // step's own walk pins the same control the same way.
        await page.getByLabel('Stars', { exact: true }).first().fill('2');

        await page.getByRole('button', { name: 'Save legacy' }).click();

        // The write lands on the compare surface for the same run, which is the "now what does this
        // look like" answer to a confirmation.
        await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });
        // The recorded values read back as stored, and the row structure is the alignment
        // design-2.0 §46 asks for. Scoped to the side-by-side region because the page now carries the
        // difference table too (slice 7 step 3.3), so an unscoped table locator resolves two.
        const table = page.getByRole('region', { name: /side by side/i }).getByRole('table');
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
        // The cell's leading figure is the count; since slice 7 step 3.3 the per-parent split prints
        // beside it as visible text rather than living in a `title`, so the pin reads the count at the
        // start of the cell and the split separately instead of requiring the cell to contain the
        // number alone.
        await expect(blueSparks.locator('td').first()).toHaveText(/^1\b/);
        await expect(blueSparks.locator('td').first()).toContainText('from Parent A');

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

        // The held figure reads as absent with its reason printed, not as a zero.
        //
        // Dated 2026-10-27 (slice 7 step 3.3): the reason moved out of a hover-only `title` and into
        // visible text, so the cell is `N/A` plus the sentence rather than `N/A` alone. The `title`
        // assertion that used to follow it is retired with the attribute it read.
        const chanceRow = table.getByRole('rowheader', { name: 'Spark chance', exact: true }).locator('..');
        // The cell's own text starts with whitespace (the `N/A` sits in a wrapping span), so the
        // pattern anchors on the words rather than on `^`.
        await expect(chanceRow.locator('td').first()).toHaveText(/N\/A\s+This tool holds no sourced star-roll table/);
        await expect(chanceRow.locator('td').first()).toContainText(/star-roll table/);

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
            await page.getByRole('button', { name: 'Save legacy' }).click();
            await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });
        }

        await page.goto(`/legacy/compare?runs[]=${first}&runs[]=${second}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const table = page.getByRole('region', { name: /side by side/i }).getByRole('table');
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

        // The wireframe's starting-cap pair (`legacy-compare.html:90-115`): one card per column, five
        // stat rows each, and Wit named because this build stores no entered Wit ceiling.
        for (const letter of ['A', 'B']) {
            const heading = page.getByRole('heading', { name: `Starting caps · legacy ${letter}`, level: 2 });
            await expect(heading).toBeVisible();

            const card = heading.locator('xpath=ancestor::section');
            await expect(card.locator('tbody tr')).toHaveCount(5);

            for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
                await expect(card.getByRole('rowheader', { name: stat })).toBeVisible();
            }
        }
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
        expect(scrollable, 'the compare region scrolls horizontally at 320px').toBe(true);

        // No horizontal overflow of the scrolling box, which is what 1.4.10 forbids.
        //
        // Measured on `body`, not `documentElement`. `main` carries `container-type: inline-size`,
        // and Chromium's documentElement.scrollWidth aggregates the contained element's overflow at
        // the document level: at 320px it reports 447 while body.scrollWidth, body.clientWidth,
        // body.offsetWidth, main.scrollWidth and main.clientWidth all report 320. A fifth probe
        // (pseudo-elements, position:fixed, transforms, text-overflow, text-indent, SVG, orphan
        // nodes) found no element escaping the viewport; the widest box is the containment of the
        // 640px table inside this region. `body` is the element that would actually scroll, so it is
        // the correct measure of the invariant.
        const overflow = await page.evaluate(
            () => document.body.scrollWidth <= document.body.clientWidth + 1,
        );
        expect(overflow, 'the page scrolls horizontally at 320px').toBe(true);
    });

    test('says so when there is nothing to compare', async ({ page }) => {
        await page.goto('/legacy/compare', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // design-2.0 §29 again: what is missing, why it matters, what to do.
        await expect(page.getByText(/Nothing selected to compare/)).toBeVisible();
        await expect(page.getByRole('table')).toHaveCount(0);
    });

    test('prints the difference in its own column, and calls a no-difference verdict "Nothing decisive"', async ({ page }) => {
        // Two runs with the same recorded selection: the difference column must exist, must print a
        // match as `same` rather than hiding the row, and the page's verdict line must say the
        // comparison is not decisive.
        const first = await createActiveRun(page, 'Special Week');
        const second = await createActiveRun(page, 'Special Week');

        for (const runId of [first, second]) {
            await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
            await page.locator('#app > *').first().waitFor();
            await page.getByLabel('Assign to Parent A').selectOption({ index: 0 });
            await page.getByRole('button', { name: 'Save legacy' }).click();
            await page.waitForURL(/\/legacy\/compare/, { waitUntil: 'domcontentloaded' });
        }

        await page.goto(`/legacy/compare?runs[]=${first}&runs[]=${second}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const differenceTable = page
            .getByRole('heading', { name: 'Where they differ' })
            .locator('xpath=following-sibling::div[1]')
            .getByRole('table');
        await expect(differenceTable).toBeVisible();
        await expect(differenceTable.getByRole('columnheader', { name: 'Difference' })).toBeVisible();

        // Matching rows still print, and they print the word `same` rather than an empty cell.
        await expect(differenceTable.getByRole('rowheader', { name: 'Trainee' })).toBeVisible();
        await expect(differenceTable.getByRole('cell', { name: 'same' }).first()).toBeVisible();

        // No difference anywhere is a permitted verdict, and it is printed in words.
        await expect(page.getByText(/Nothing decisive/)).toBeVisible();
    });
});
