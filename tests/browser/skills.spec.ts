import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

// Rendered-copy evidence for the ported Skills screens (SCR-SKL-001/002, ADR-0020 §1). The
// server-side tests (SkillSearchScreenTest, SkillDetailTest) assert the resolved props; these
// assert what the browser shows. Runs against the app's own scratch database
// (playwright.config.ts), so the rows are the seeded ones: `#LookatCurren` (id 370) is a
// unique-class skill whose two name columns agree and whose SP cost is null, and
// `1,500,000 CC` (id 330) carries a Japanese name that differs from the client string.

const UNIQUE_BADGE_DISCLOSURE =
    "Marked from the source's skill class code, not from a card's own skill list.";
const NO_SP_COST_TITLE = 'The source states no SP cost for this class of skill.';

test('links the Skills screen from the primary navigation and marks it current', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    // The desktop sidebar renders the same destinations as the mobile bottom bar; the
    // sidebar is the visible one at this viewport.
    const link = page.locator('aside').getByRole('link', { name: 'Skills' });
    await expect(link).toBeVisible();
    await expect(link).toHaveAttribute('aria-current', 'page');
});

test('sizes the search controls to the 44px contract and names the miss', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    // KI-29 / DESIGN.md §6.14: the search field, the type picker and the submit are 44px tall.
    for (const name of ['Search', 'Unique', 'Type']) {
        const box = await page.getByLabel(name).boundingBox();
        expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Filter' }).boundingBox();
    expect(submit?.height ?? 0, 'the Filter button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    // D-65: a search that missed says so and names the ask, rather than reading as an empty catalog.
    await page.goto('/skills?search=nothinghere');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Nothing matches/)).toBeVisible();
});

test('prints a Japanese name in its own language on the index row', async ({ page }) => {
    await page.goto(`/skills?search=${encodeURIComponent('1,500,000 CC')}`);
    await page.locator('#app > *').first().waitFor();

    // WCAG 3.1.2 Language of Parts: the kana carry lang="ja" so a screen reader switches voice.
    await expect(page.locator('#skill-results [lang="ja"]', { hasText: '十万バリキ' })).toBeVisible();
});

test('renders the condition column and states that the cost is the base cost', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    // The engine condition has its own column, in monospace beside the readable name.
    await expect(page.getByRole('columnheader', { name: 'Condition (engine)' })).toBeVisible();
    await expect(page.locator('#skill-results tr').first().locator('td.font-mono')).toHaveCount(2);

    // The hint discount is a per-run fact, so the library states the base cost and says so.
    await expect(page.getByText(/Cost is the base cost only/)).toBeVisible();
    await expect(page.getByText(/Search matches the skill's name/)).toBeVisible();
});

test('follows a search row to the detail page and states the absences there', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();

    // The detail URL carries the list's query and the list it came from, so the way back can resume it.
    await expect(page).toHaveURL(/\/skills\/370\?.*search=lookatcurren/);
    await expect(page.getByRole('heading', { name: '#LookatCurren', level: 1 })).toBeVisible();

    // The two name columns agree on this row, so the pair prints once: no second slot.
    await expect(page.locator('main [lang="ja"]')).toHaveCount(0);

    // D-256: the derived badge prints its rule and whose derivation it is.
    const badge = page.locator(`[title="${UNIQUE_BADGE_DISCLOSURE}"]`);
    await expect(badge).toBeVisible();
    await expect(badge).toHaveText('✦ Unique');

    // The export's class code, printed as the integer only (ADR-0011 §3 as amended). Skill 370 is 5.
    await expect(
        page.getByRole('rowheader', { name: 'Class code', exact: true }).locator('xpath=following-sibling::td[1]'),
    ).toHaveText('5');

    // The Related card's three rows, each a count or a stated absence.
    for (const name of ['Same condition', 'Same class code', 'Same class code and type']) {
        await expect(page.getByRole('rowheader', { name, exact: true })).toBeVisible();
    }

    // D-220: an absent SP cost is the shared disclosure, with the reason behind its summary.
    const spCost = page
        .locator('section[aria-labelledby="mechanics"] summary')
        .filter({ hasText: NO_SP_COST_TITLE });
    await expect(spCost).toBeVisible();
    await expect(spCost).toHaveText(/^\s*N\/A, The source states no SP cost/);

    // The page's own sections and its way back. Engine tokens lead (plan Slice 8), so the
    // condition table is a visible card and not a fold.
    await expect(page.getByRole('heading', { name: "The source's own engine data", level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Held by trainees', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();

    // Prose is second and it is an empty state: no client string for a skill description exists, so
    // the block says so rather than paraphrasing the export's English.
    await expect(page.getByRole('heading', { name: 'Readable description', level: 2 })).toBeVisible();
    await expect(page.getByText('No client string.')).toBeVisible();
    // The HFW's own label for the way back ("skill-detail.html:62").
    await expect(page.getByRole('link', { name: 'Back to search' })).toBeVisible();
});

test('the skill detail prints the engine tokens first, with no fold over them', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();
    await expect(page).toHaveURL(/\/skills\/370\?.*search=lookatcurren/);
    await page.locator('#app > *').first().waitFor();

    // The condition table is the primary content, so it is not behind a disclosure: a fold would
    // make the one representation that can be diffed against the export the second thing read. The
    // absences elsewhere in the card use disclosures, so this checks the condition CELL itself.
    const mechanics = page.locator('section[aria-labelledby="mechanics"]');
    const conditionCell = mechanics
        .getByRole('rowheader', { name: 'Condition', exact: true })
        .locator('xpath=following-sibling::td[1]');
    await expect(conditionCell).toBeVisible();
    expect(
        await conditionCell.evaluate((cell) => cell.querySelector('details') === null),
        'the engine condition is printed directly, not behind a fold',
    ).toBe(true);
    await expect(mechanics).toContainText('Base cost');
    await expect(mechanics).toContainText('not client labels');
});

test('the skill index states the page it is showing', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    // The canonical pager states the range and the page. The pager it replaced was a set of links
    // and, on a short list, rendered nothing at all.
    const status = page.locator('nav[aria-label="Pagination"] [role="status"]');
    await expect(status).toContainText(/Showing \d+ to \d+ of \d+ skills/);
    await expect(status).toContainText(/Page 1 of \d+/);
});

// A-94 (plan 1.2): the activation condition is an engine expression with no break opportunities, so
// the `<dd>` holding it once pushed the document 224px wide at 390. `break-words min-w-0` plus the
// same on the grid items is the fix; this measures the geometry rather than trusting the class name.
test('does not scroll the detail page sideways at 390 or 768', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();
    await expect(page).toHaveURL(/\/skills\/370\?.*search=lookatcurren/);
    await page.locator('#app > *').first().waitFor();

    for (const width of [390, 768]) {
        await page.setViewportSize({ width, height: 900 });
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, `the skill detail scrolls sideways at ${width}px`).toBeLessThanOrEqual(0);
    }
});

test('filters to the unique-class rows and shows the badge on the result', async ({ page }) => {
    await page.goto('/skills?unique=1');
    await page.locator('#app > *').first().waitFor();

    // The badge is a claim about the class code, so the filter and the badge agree.
    await expect(page.locator(`#skill-results [title="${UNIQUE_BADGE_DISCLOSURE}"]`).first()).toBeVisible();
});

/*
 * The skills index's own axe scan (plan slice 8.8b).
 *
 * `/skills` is a route of its own with a page of its own, and the Database hub's `/database/skills`
 * is a different page: a scan on one says nothing about the other.
 */
test('the skills index passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * The skill DETAIL page's own axe scan (plan slice 8.8c).
 *
 * `/skills/{skill}` is a route of its own, and the case that reaches it gets there by CLICKING a
 * search result, so the corpus holds no literal navigation to it. The route-coverage guard matches
 * a route against the corpus's literals, so the detail page needs one of its own - and it needs a
 * scan, since the index page's scan says nothing about the detail page's markup.
 *
 * The id is the export's own for `#LookatCurren`, the skill the case above follows.
 */
test('the skill detail passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/skills/370');
    await page.locator('#app > *').first().waitFor();

    // The heading is asserted first so a wrong id fails as a missing page rather than passing as
    // a clean scan of whatever error page the route fell back to.
    await expect(page.getByRole('heading', { name: '#LookatCurren', level: 1 })).toBeVisible();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * The filter row's order and the condition note's home, both read off the HFW
 * (`skills.html:72-78`, `:132-137`). Two deliberate absences ride with them, recorded in the page's
 * own HFW-OVERRIDE blocks: no Distance facet (no column answers it) and name-only search (no readable
 * effect text is stored).
 */
test('orders the filters as the HFW draws them and keeps the condition note inside the table card', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    const form = page.locator('form').filter({ has: page.getByRole('button', { name: 'Filter' }) });
    await expect(form.locator('label').nth(0)).toContainText('Search');
    await expect(form.locator('label').nth(1)).toContainText('Unique');
    await expect(form.locator('label').nth(2)).toContainText('Type');

    const unique = page.getByLabel('Unique');
    await expect(unique).toHaveValue('');
    await expect(unique.locator('option')).toHaveText(['Any', 'Unique only']);
    await expect(unique.locator('option[value=""]')).toBeEnabled();
    await expect(page.getByLabel('Type').locator('option[value=""]')).toHaveCount(1);
    await expect(page.getByLabel('Type').locator('option[value=""]')).toBeEnabled();
    await expect(page.getByLabel('Type').locator('option[value=""]')).toHaveText('Any');

    await expect(page.getByLabel('Distance'), 'a Distance facet the catalogue cannot answer').toHaveCount(0);

    // The table scrolls inside its own labelled, focusable region; the note and pager stay in the card
    // outside it, so neither scrolls sideways with the columns.
    const card = page.locator('div.card').filter({ has: page.locator('#skill-results') });
    const region = card.locator('div[role="region"]');
    await expect(region).toHaveAttribute('aria-label', 'Skill catalog, scrollable');
    await expect(region).toHaveAttribute('tabindex', '0');

    const note = card.locator('p.note');
    await expect(note).toContainText("The condition column prints the export's own tokens");
    expect(
        await note.evaluate((node) => node.closest('[role="region"]') === null),
        'the note sits in the card, not in the scroll region',
    ).toBe(true);

    const noteBottom = await note.evaluate((node) => node.getBoundingClientRect().bottom);
    const pagerTop = await card.locator('nav[aria-label="Pagination"]').evaluate((node) => node.getBoundingClientRect().top);
    expect(noteBottom, 'the note sits above the pager').toBeLessThanOrEqual(pagerTop);

    // At 320 the table overflows inside the region and the document does not (the region's own
    // scrollWidth/clientWidth pair is the layout-compositions assertion; this proves it here too).
    await page.setViewportSize({ width: 320, height: 900 });
    expect(await region.evaluate((el) => el.scrollWidth > el.clientWidth)).toBe(true);
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, '/skills scrolls the document sideways at 320px').toBeLessThanOrEqual(1);
});

test('shows the invitation only before a search is made', async ({ page }) => {
    // The line shipped with the shared body but never rendered: the guard compared a string ref to null.
    // It belongs to the state where nobody has asked yet (D-65).
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Type part of a skill name to narrow the catalog/)).toBeVisible();

    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Type part of a skill name to narrow the catalog/)).toHaveCount(0);
});

test('shows absence disclosures only for missing skill values', async ({ page }) => {
    const response = await page.goto('/skills');
    expect(response?.ok()).toBe(true);
    await expect(page.locator('#skill-results tr')).not.toHaveCount(0);

    const populated = page.locator('#skill-results tr');
    let populatedCells = 0;
    for (const row of await populated.all()) {
        // The name cell is a row header, so the value cells are td indices 0..2 (type, condition, cost).
        for (const column of [0, 1, 2]) {
            const cell = row.locator('td').nth(column);
            const summaries = cell.locator('summary');
            if ((await summaries.count()) === 1) {
                // The summary's text carries the template's indentation, so the pattern allows the
                // whitespace before the marker and then requires the comma and a reason after it.
                await expect(summaries).toHaveText(/^\s*N\/A, \S/);
                await expect(cell.locator('[title]')).toHaveCount(0);
                continue;
            }
            populatedCells++;
            await expect(cell).not.toContainText('N/A');
        }
    }
    expect(populatedCells).toBeGreaterThan(0);

    await page.goto('/skills?search=lookatcurren');
    const missingCost = page.locator('#skill-results summary')
        .filter({ hasText: 'The source states no SP cost for this class of skill.' });
    await expect(missingCost.first()).toBeVisible();
    await expect(missingCost.first()).toHaveText(/N\/A, The source states no SP cost/);
});

test('prices the hint ladder from the skill\'s own base, floored', async ({ page }) => {
    // The wireframe's ladder (skill-detail.html:96-114): the client's measured discounts priced from this
    // skill's own base. The old pin here asserted that no price was derived; the owner now requires the
    // ladder priced, so that assertion is deliberately retired and replaced by the exact figures below.
    // No row is "current": no table stores a per-run hint level.
    await page.goto('/skills/1');
    await page.locator('#app > *').first().waitFor();

    const ladder = page
        .getByRole('heading', { name: 'Displayed cost at each hint level' })
        .locator('xpath=following::table[1]');
    await expect(ladder).toBeVisible();
    await expect(ladder.getByRole('row')).toHaveCount(7);
    await expect(ladder.getByRole('columnheader')).toHaveText(['Hint', 'Discount', 'Displayed cost']);

    // Skill 1's base is 180: None, Lv1, Lv2, Lv3, Lv4, Lv Max.
    for (const [row, price] of [[1, '180'], [2, '162'], [3, '144'], [4, '126'], [5, '117'], [6, '108']] as const) {
        await expect(ladder.getByRole('row').nth(row).locator('td').nth(1)).toHaveText(price);
    }

    // The wireframe's own base (170): Lv1 153 ... Lv Max 102.
    await page.goto('/skills/6');
    await page.locator('#app > *').first().waitFor();
    const own = page
        .getByRole('heading', { name: 'Displayed cost at each hint level' })
        .locator('xpath=following::table[1]');
    await expect(own.getByRole('row').nth(2).locator('td').nth(1)).toHaveText('153');
    await expect(own.getByRole('row').nth(5).locator('td').nth(1)).toHaveText('110');
    await expect(own.getByRole('row').nth(6).locator('td').nth(1)).toHaveText('102');

    // The note states the rule, the one rounding case, and that no run stores a hint level.
    await expect(page.getByText(/130 × 0\.65 = 84\.5 is shown as 84/)).toBeVisible();
    await expect(page.getByText(/No run stores a hint level/)).toBeVisible();

    // A value and its absence are mutually exclusive in the Base-cost cell.
    const costCell = page
        .getByRole('rowheader', { name: 'Base cost' })
        .locator('xpath=following-sibling::td[1]');
    await expect(costCell).toContainText('SP');
    await expect(costCell).not.toContainText('N/A');

    // The absent case: no base, so the Displayed cost column is absent and the note says why.
    await page.goto('/skills/370');
    await page.locator('#app > *').first().waitFor();
    const absentLadder = page
        .getByRole('heading', { name: 'Displayed cost at each hint level' })
        .locator('xpath=following::table[1]');
    await expect(absentLadder.getByRole('columnheader')).toHaveText(['Hint', 'Discount']);
    await expect(page.getByText(/no hint-level price can be computed/)).toBeVisible();
    const absentCell = page
        .getByRole('rowheader', { name: 'Base cost' })
        .locator('xpath=following-sibling::td[1]');
    await expect(absentCell).toContainText('N/A');
    // Its reason names the SP cost, so the absence assertion is "no numeric SP figure".
    await expect(absentCell).not.toContainText(/\d+ SP/);
});

test('prints the class code, the related counts and the head facts', async ({ page }) => {
    await page.goto('/skills/6');
    await page.locator('#app > *').first().waitFor();

    // The export's integer only; no rarity word is claimed for it (ADR-0011 §3 as amended).
    await expect(
        page.getByRole('rowheader', { name: 'Class code', exact: true }).locator('xpath=following-sibling::td[1]'),
    ).toHaveText('1');

    // The three Related rows, and the type-filter link labelled for what it filters.
    for (const name of ['Same condition', 'Same class code', 'Same class code and type']) {
        await expect(page.getByRole('rowheader', { name, exact: true })).toBeVisible();
    }
    await expect(page.getByRole('link', { name: 'Search the skills of this type' })).toBeVisible();

    // The lede states the type, the base and the style clause read from the conditions.
    const lede = page.locator('[data-region="lede"]');
    await expect(lede).toContainText('Speed · 170 Skill Points at base.');
    await expect(lede).toContainText('No running-style precondition of its own.');
});

test('states that no run records the skill when the run table is empty', async ({ page }) => {
    // The browser scratch database is seeded without runs by contract (tests/browser/global-setup.ts);
    // the populated card is pinned by SkillDetailTest and shown in the dev-database screenshot.
    await page.goto('/skills/370');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: 'In your runs', level: 2 })).toBeVisible();
    await expect(page.getByText('No run records this skill.')).toBeVisible();
});

test('returns to the filtered list with its query intact', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren&unique=1');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();
    await expect(page).toHaveURL(/\/skills\/370\?.*search=lookatcurren.*unique=1/);

    await page.getByRole('link', { name: 'Back to search' }).click();
    await expect(page).toHaveURL(/\/skills\?.*search=lookatcurren.*unique=1/);
    await expect(page.getByLabel('Search')).toHaveValue('lookatcurren');
    await expect(page.getByLabel('Unique')).toHaveValue('1');
});
