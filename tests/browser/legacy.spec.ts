import { test, expect } from '@playwright/test';

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
 * No axe pass here: `@axe-core/playwright` is not a dependency and adding one needs owner approval
 * (`AGENTS.md` §5). The plan's fallback applies, and the checks below are the hand-rolled ones §12.2
 * names — target size, keyboard reachability, `lang` on Japanese text, and absence rendered as
 * `N/A` rather than as a dash.
 */

const RECORD_ONLY_NOTICE =
    'Record only. This screen stores and compares what you enter. It does not compute inheritance.';

/** The four Spark kinds the `[Global]` client renders (REFERENCE §1.5.2). */
const SPARK_KINDS = ['Blue', 'Pink', 'Green', 'White', 'Scenario'];

/**
 * Create one `Active` run through the app's own form, and return its id.
 *
 * The trainee is chosen by typing into the combobox rather than by posting a request, so the run
 * exists the way a Trainer's would. `Runs/Create.vue` requires a trainee and defaults the status to
 * `Active`, which is the only status the Legacy builder opens on (`ADR-0010` D-260 fixity).
 */
async function createActiveRun(page: import('@playwright/test').Page, traineeName: string): Promise<number> {
    await page.goto('/training-runs/create');
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
    await page.waitForURL(/\/training-runs\/\d+$/);

    const id = Number(page.url().match(/\/training-runs\/(\d+)$/)?.[1]);
    expect(id, 'the created run has no id in its URL').toBeGreaterThan(0);

    return id;
}

test.describe('Legacy Lab browse', () => {
    test('carries the record-only banner and states the empty library in words', async ({ page }) => {
        await page.goto('/legacy');
        await page.locator('#app > *').first().waitFor();

        // The banner is a `role="note"` standing caveat with a decorative glyph, so a screen reader
        // hears the sentence once and the glyph is never read as content.
        const notice = page.getByRole('note');
        await expect(notice).toHaveText(RECORD_ONLY_NOTICE);

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
        await page.goto('/legacy');
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
        await page.goto('/legacy');
        await page.locator('#app > *').first().waitFor();

        // The sidebar is the visible nav at the default viewport; the mobile bar renders the same
        // nine destinations below `md`.
        const link = page.locator('aside').getByRole('link', { name: 'Legacy Lab' });
        await expect(link).toBeVisible();
        await expect(link).toHaveAttribute('aria-current', 'page');

        // Miller's Law (plan §13): the nav is nine destinations with one named absence. D5 filled the
        // Legacy Lab placeholder rather than appending a tenth, so the count is unchanged and the
        // Veterans entry is the remaining absence.
        await expect(page.locator('aside').getByText('Veterans')).toHaveAttribute('title', 'Coming with Trainer Desk 2.0');
    });
});

test.describe('Legacy Lab builder', () => {
    test('draws the six-node graph, assigns by keyboard and states every absence', async ({ page }) => {
        const runId = await createActiveRun(page, 'Special Week');

        await page.goto(`/legacy/${runId}`);
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('note')).toHaveText(RECORD_ONLY_NOTICE);

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

        await page.goto(`/legacy/${runId}`);
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
        await page.waitForURL(/\/legacy\/compare/);
        await expect(page.getByRole('note')).toHaveText(RECORD_ONLY_NOTICE);

        // The recorded values read back as stored, and the row structure is the alignment
        // design-2.0 §46 asks for.
        const table = page.getByRole('table');
        await expect(table).toBeVisible();

        // The Spark chip carries colour AND label AND star count, so nothing is conveyed by colour
        // alone (WCAG 1.4.1). The accessible name spells all three.
        const chip = page.getByText('Blue', { exact: true }).first();
        await expect(chip).toBeVisible();
        await expect(page.locator('table').getByText('Speed').first()).toBeVisible();

        // Every stored property is a row header, and the absent ones are N/A with a reason rather
        // than a dash or a zero.
        for (const row of [
            'Trainee',
            'Parent A',
            'Parent A rank',
            'Parent A ancestors',
            'Parent B',
            'Spark chance',
        ]) {
            await expect(table.getByRole('rowheader', { name: row })).toBeVisible();
        }

        await expect(table.getByRole('rowheader', { name: 'Spark chance' })).toBeVisible();
        await expect(table.getByText('N/A').first()).toBeVisible();
    });
});

test.describe('Legacy Lab compare', () => {
    test('aligns up to four columns in rows and never ranks them', async ({ page }) => {
        // Two runs, so there is something to align. One column is a comparison of nothing, and the
        // page says so rather than printing a lone column.
        const first = await createActiveRun(page, 'Special Week');
        const second = await createActiveRun(page, 'Tokai Teio');

        for (const runId of [first, second]) {
            await page.goto(`/legacy/${runId}`);
            await page.locator('#app > *').first().waitFor();
            await page.getByLabel('Assign to Parent A').selectOption({ index: 0 });
            await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
            await page.waitForURL(/\/legacy\/compare/);
        }

        await page.goto(`/legacy/compare?runs[]=${first}&runs[]=${second}`);
        await page.locator('#app > *').first().waitFor();

        const table = page.getByRole('table');
        await expect(table).toBeVisible();

        // Both runs are column headers, so the properties line up under them (design-2.0 §46:
        // "never force the user to compare two separate cards mentally").
        await expect(table.getByRole('columnheader').nth(1)).toContainText(`Run #${first}`);
        await expect(table.getByRole('columnheader').nth(2)).toContainText(`Run #${second}`);

        // Every property is a row header, and the row set is identical for each column: that is what
        // "aligned in rows" means, and a screen reader announces the property with every cell.
        const rowHeaders = table.getByRole('rowheader');
        await expect(rowHeaders).toHaveCount(12);

        // No total, no score, no "best" marker. Each of those would rank configurations on a figure
        // the tool does not hold (ADR-0020 §3), and the caption says so in the Trainer's own words.
        await expect(page.getByText(/nothing is scored, ranked or totalled/i)).toBeVisible();
        await expect(table.getByText(/best/i)).toHaveCount(0);
        await expect(table.getByText(/score/i)).toHaveCount(0);
    });

    test('scrolls the comparison at 320px without hiding the right-hand columns', async ({ page }) => {
        const run = await createActiveRun(page, 'Special Week');

        await page.goto(`/legacy/compare?runs[]=${run}`);
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
        await page.goto('/legacy/compare');
        await page.locator('#app > *').first().waitFor();

        // design-2.0 §29 again: what is missing, why it matters, what to do.
        await expect(page.getByText(/Nothing selected to compare/)).toBeVisible();
        await expect(page.getByRole('table')).toHaveCount(0);
    });
});
