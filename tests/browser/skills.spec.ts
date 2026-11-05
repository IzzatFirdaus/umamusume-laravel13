import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { PAGE_MARKERS, assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { inventoryReleaseStatusCounts, removeNoDescriptionSkill, removeScopeFixtures, seedNoDescriptionSkill, seedScopeFixtures } from '../utils/seed-range-operator';

const WIREFRAME = pathToFileURL(
    resolve('docs/design-research/frontend-v2-wireframes/skills.html'),
).href;

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

    // D-256: the derived badge prints its rule and whose derivation it is. The badge carries an
    // sr-only clause naming the rule, so the visible word is asserted by containment and the
    // clause is asserted as text — an exact match would fail on the accessible suffix, which is
    // the part that makes the mark readable to a screen reader.
    const badge = page.locator(`[title="${UNIQUE_BADGE_DISCLOSURE}"]`);
    await expect(badge).toBeVisible();
    await expect(badge).toContainText('✦ Unique');
    await expect(badge).toContainText("from the source's skill class code, not a rarity word");

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

    // Prose is second. This row HAS a client string since the re-import filled `desc_en`, so the
    // block prints it and names its source; the "No client string." empty state is a separate
    // case below, seeded, because the reference data no longer holds a Global row without one.
    await expect(page.getByRole('heading', { name: 'Readable description', level: 2 })).toBeVisible();
    await expect(page.locator('section[aria-labelledby="description"]')).toContainText('From GameTora export');
    await expect(page.locator('section[aria-labelledby="description"]')).not.toContainText('No client string.');
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
    await expect(status).toContainText(/Showing \d+–\d+ of \d+ skills/);
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
 * (`skills.html:72-78`, `:132-137`), with the two deliberate departures recorded in the page's own
 * HFW-OVERRIDE blocks: name-only search (no readable effect text is stored) and the Distance facet's
 * sentinel wording (SHR-1). The Scope facet is this slice's own addition and has no HFW counterpart,
 * so it leads the row (`R1`: the scope narrows what the other four facets narrow).
 */
test('orders the filters as the HFW draws them and keeps the condition note inside the table card', async ({ page }) => {
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();

    const form = page.locator('form').filter({ has: page.getByRole('button', { name: 'Filter' }) });
    // Scope first (this slice), then the wireframe's own four in its order.
    await expect(form.locator('label').nth(0)).toContainText('Scope');
    await expect(form.locator('label').nth(1)).toContainText('Search');
    await expect(form.locator('label').nth(2)).toContainText('Unique');
    await expect(form.locator('label').nth(3)).toContainText('Type');

    const unique = page.getByLabel('Unique');
    await expect(unique).toHaveValue('');
    await expect(unique.locator('option')).toHaveText(['Any', 'Unique only']);
    await expect(unique.locator('option[value=""]')).toBeEnabled();
    await expect(page.getByLabel('Type').locator('option[value=""]')).toHaveCount(1);
    await expect(page.getByLabel('Type').locator('option[value=""]')).toBeEnabled();
    await expect(page.getByLabel('Type').locator('option[value=""]')).toHaveText('Any');

    // The Distance facet exists since P3: `distance_type` is a parsed column now, so the control
    // the wireframe draws is answerable. Its sentinel is the string 'Unspecified', never "0"
    // (SHR-1) — asserted here because this is the catalogue's only distance control.
    const distance = page.getByLabel('Distance');
    await expect(distance).toHaveCount(1);
    await expect(distance.locator('option')).toHaveText(['Any', 'Sprint', 'Mile', 'Medium', 'Long', 'Unspecified']);
    await expect(distance.locator('option[value="0"]')).toHaveText('Unspecified');

    // The table scrolls inside its own labelled, focusable region; the condition note and pager stay
    // in the card outside it, so neither scrolls sideways with the columns. The card now holds two
    // `p.note` blocks (the provenance footnote above the region, the condition note below it), so the
    // locator names the condition note by its own text rather than taking the first one.
    const card = page.locator('div.card').filter({ has: page.locator('#skill-results') });
    const region = card.locator('div[role="region"]');
    await expect(region).toHaveAttribute('aria-label', 'Skill catalog, scrollable');
    await expect(region).toHaveAttribute('tabindex', '0');

    const note = card.locator('p.note').filter({ hasText: "The condition column prints the export's own tokens" });
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



/*
 * Structural parity for SCR-SKL-001 (`/skills`) against `skills.html`, the same instrument
 * `veterans-parity.spec.ts` runs for the library: ordered markers resolved from `data-region`
 * first and the ported class tokens second, so the comparison survives a font metric or a
 * padding change and a missing block does not.
 *
 * The wireframe's filter row draws four `.field` labels; this catalogue carries five. The extra is the
 * Scope facet this slice adds (`R1`), which has no wireframe counterpart, so it is counted separately
 * rather than folded into the comparison. The four the wireframe draws are Search, Unique, Type and
 * Distance, and the count stays an assertion of its own so a facet that silently disappears fails.
 */
test('renders the wireframe regions in the wireframe order at 1280', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/skills', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const app = await capture(page, 'main', { markers: PAGE_MARKERS });

    // Five: the wireframe's four, plus this slice's Scope facet.
    expect(app.regions.filter((marker) => marker === 'field'), 'scope, search, type, unique and distance').toHaveLength(5);

    await page.goto(WIREFRAME);
    const hfw = await capture(page, '.canvas', { markers: PAGE_MARKERS });

    assertStructurallyEqual(
        app,
        { ...hfw, regions: hfw.regions.filter((marker) => marker !== 'field') },
        { regions: 'subset', grid: false },
    );
});

/*
 * Inv-9 (DOM half): a differing compare row carries the word AND the glyph, never colour alone.
 *
 * `InvariantSelfCheckTest::test_inv9_differs_word_and_glyph` holds the payload and the template
 * source; this reads the rendered markup, because the invariant is about what a Trainer sees.
 */
test('marks a differing compare row with the word and the glyph, never colour alone', async ({ page }) => {
    await page.goto('/skills?search=Gourmand');
    await page.locator('#app > *').first().waitFor();

    const row = page.getByRole('row').filter({ hasText: 'Gourmand' }).first();
    await expect(row).toBeVisible();

    const detailHref = await row.getByRole('link').first().getAttribute('href');
    expect(detailHref).toBeTruthy();

    // The row's own link already carries the search suffix, so the compare param is appended to
    // the parsed URL rather than by string concatenation (which would produce `?search=x?compare=y`).
    const url = new URL(detailHref as string, 'http://127.0.0.1');
    url.searchParams.set('compare', '2');

    // Compare against a skill the first row differs from on type (Gourmand is Recovery, id 2 is Speed).
    await page.goto(`${url.pathname}${url.search}`);
    await page.locator('#app > *').first().waitFor();

    const marker = page.locator('[data-differs]').first();
    await expect(marker).toBeVisible();
    await expect(marker).toContainText('differs');
    await expect(marker).toContainText('≠');

    // No colour-only state: the marker is text, so it survives a greyscale render.
    const colour = await marker.evaluate((el) => getComputedStyle(el).color);
    expect(colour).not.toBe('rgb(0, 0, 0)');
});


/*
 * Inv-6 (DOM half): a Global row with no client string says so and invents nothing.
 *
 * The reference seed no longer holds such a row — the re-import filled `desc_en` for all 623
 * Global skills — so the empty state is exercised against a seeded fixture. A fallback that
 * paraphrased, or printed an empty paragraph, fails here.
 */
test('prints No client string. for a Global row with no description', async ({ page }) => {
    seedNoDescriptionSkill();

    try {
        await page.goto('/skills?search=no-string-fixture');
        await page.locator('#app > *').first().waitFor();

        await page.locator('#skill-results').getByRole('link', { name: 'No String Fixture' }).click();

        const panel = page.locator('section[aria-labelledby="description"]');
        await expect(panel).toBeVisible();
        await expect(panel).toContainText('No client string.');

        // No invented prose: the panel's only sentence is the stated absence plus its note.
        await expect(panel.locator('.text-sm.text-ink')).toHaveText('No client string.');
    } finally {
        removeNoDescriptionSkill();
    }
});

/*
 * P3: search matches the skill's name OR its stored effect text.
 *
 * "Gourmand" has a description that says "Recover endurance"; searching "recover" must find it even
 * though the word is not in the name. The catalogue's own footer states this ("Search matches the
 * skill's name or the stored effect text"), so a name-only search would make that sentence false.
 */
test('finds a row by its description when the name does not match', async ({ page }) => {
    await page.goto('/skills?search=recover');
    await page.locator('#app > *').first().waitFor();

    const results = page.locator('#skill-results');
    await expect(results).toBeVisible();

    // Gourmand's description begins "Recover endurance mid-race." and its name contains no "recover".
    const gourmand = results.getByRole('link', { name: 'Gourmand' });
    await expect(gourmand).toBeVisible();

    // The match is on the stored effect text, so the row's name is untouched.
    await expect(gourmand).not.toContainText('recover');
});

/*
 * The distance seam's client half (R5).
 *
 * The server filters `?distance=2` to Mile rows and sends the value back as an int prop. This case is
 * the only thing that can see whether the prop survives that last hop: `SkillCatalog.vue` read
 * `props.distance` without declaring it in `defineProps`, so Vue dropped the server's value into
 * `$attrs` and the select painted "Any" over Mile-only rows. `npm run typecheck` cannot see it
 * (`tsc --noEmit` does not read `.vue`), which is why the assertion lives here.
 *
 * It also covers the server half from the user's side: the Filter click posts the form again, so a
 * regression that re-introduced the `strict_types` TypeError would fail this click as a 500.
 */
test('paints the Distance facet from the URL and keeps it through a filter', async ({ page }) => {
    await page.goto('/skills?distance=2');
    await page.locator('#app > *').first().waitFor();

    // The select reflects the request, not its default: this is the props drop.
    await expect(page.getByLabel('Distance')).toHaveValue('2');
    await expect(page.getByLabel('Distance')).not.toHaveValue('');

    // The rows are the Mile rows, so the control and the table agree. The names are read from the
    // scratch database's own mileage split (25 Mile rows at the current import), not assumed:
    // 'Acceleration' is `distance_type=2`, and a Sprint row is asserted absent so this is a
    // narrowing rather than a presence check.
    const results = page.locator('#skill-results');
    await expect(results.getByRole('link', { name: 'Acceleration', exact: true })).toBeVisible();
    await expect(results.getByRole('link', { name: 'Blinding Flash', exact: true })).toHaveCount(0);

    // Re-submitting the form is the path that reached the type error before the cast: the select's
    // value goes back through the query string and into the same controller argument.
    const distance = page.getByLabel('Distance');
    await distance.selectOption('2');
    const response = page.waitForResponse((r) => r.url().includes('/skills') && r.request().method() === 'GET');
    await page.getByRole('button', { name: 'Filter' }).click();

    expect((await response).status(), 'the filtered request answered').toBe(200);
    await expect(distance).toHaveValue('2');
});

/*
 * A2 — the scope selector, at the surface a Trainer reads.
 *
 * The feature test (`SkillScopeFilterTest`) proves the controller answers three scopes. This proves the
 * control that selects one is wired to that answer, which is a different failure: the props can carry
 * `scope` and the select can still paint its first option over filtered rows — the exact shape of the
 * `distance` defect in `53bf93c3`.
 *
 * The JP row is seeded with a Japanese `name_ja` and no client name, because ruling A shows the source
 * string on that scope. A fixture with a Latin `name_ja` would pass the count assertion while proving
 * nothing about which column the row prints.
 */
test('the scope selector narrows the catalogue and survives a reload', async ({ page }) => {
    seedScopeFixtures();

    try {
        // Default scope: the Global subset. The select states it, and the JapanOnly fixture is absent.
        await page.goto('/skills?search=row');
        await page.locator('#app > *').first().waitFor();

        const scope = page.getByLabel('Scope');
        await expect(scope).toHaveValue('global');
        await expect(scope.locator('option')).toHaveText(['Global only', 'Every skill', 'Japan only']);

        const results = page.locator('#skill-results');
        await expect(results.getByRole('link', { name: 'Client Named Row' })).toBeVisible();
        // The JP fixture's source string is not reachable at this scope, which is what "narrowed" means.
        await expect(results.getByRole('link', { name: '掲げよ、己が魂の剣を！' })).toHaveCount(0);

        // Japan only: the row appears, printed as the source's own string, with `lang="ja"` because the
        // string is Japanese (ruling 2 — the attribute describes the content, not the field).
        //
        // Narrowed by a phrase from the fixture's own description rather than by its name, and that is
        // the search gap this slice records rather than a test convenience: `match_key` is built from
        // `name`, so a name search cannot reach a JP row whose only printed name is Japanese. The
        // description key is reachable at any scope (`query()` matches `match_key` OR
        // `description_match_key`), so this narrows to one row without asserting a path that does not
        // exist. Without a narrowing the fixture sits among 1,287 JapanOnly rows ordered by `name` and
        // never appears on page 1.
        await page.goto('/skills?scope=japan_only&search=' + encodeURIComponent('an english sentence'));
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByLabel('Scope')).toHaveValue('japan_only');

        const jpName = results.getByRole('link', { name: '掲げよ、己が魂の剣を！' });
        await expect(jpName).toBeVisible();
        await expect(jpName).toHaveAttribute('lang', 'ja');

        // The client name is not printed at this scope: it is the export's `enname`, which the Air
        // Messiah ruling bars from UI copy.
        await expect(results).not.toContainText("Raise My Soul's Blade!");

        // The footnote says why the row has no English name, so the absence reads as a fact.
        const footer = page.getByTestId('catalogue-footer');
        await expect(footer).toContainText('The Global client has not received these skills.');

        // The Global fixture is out of this scope entirely, so it supplies no row to inspect for `lang`.
        await expect(results.getByRole('link', { name: 'Client Named Row' })).toHaveCount(0);

        // Reload: the scope is the URL's, not component state that a fresh load would reset.
        await page.reload();
        await page.locator('#app > *').first().waitFor();
        await expect(page.getByLabel('Scope')).toHaveValue('japan_only');
        expect(page.url()).toContain('scope=japan_only');
    } finally {
        removeScopeFixtures();
    }
});

test('a misspelled scope is refused rather than answered with a wider catalogue', async ({ page }) => {
    // R2: a typo must not silently widen. The refusal is the form request's redirect with a field error
    // (`SkillSearchRequest`, and the precedent in `SkillSearchScreenTest`), so the browser lands back on
    // the catalogue with the Scope field naming the problem rather than on a 3x-larger list.
    const response = await page.goto('/skills?scope=glboal');
    await page.locator('#app > *').first().waitFor();

    expect(response?.status(), 'a refused scope still renders a page, not an error').toBe(200);

    // The refusal reaches the control that caused it: `BaseSelect` sets `aria-invalid` from its `error`
    // prop, and `SkillSearchRequest` names the field. Asserting the error state rather than the word
    // "scope" is the difference between proving the refusal landed and matching the select's own label.
    await expect(page.getByLabel('Scope')).toHaveAttribute('aria-invalid', 'true');

    // And the catalogue did not answer with every row: the default scope is still what painted.
    await expect(page.getByLabel('Scope')).toHaveValue('global');
});

/*
 * B5 — the header's provenance counts, read off the DOM and cross-checked against the inventory.
 *
 * `SkillScopeFilterTest` proves the controller answers full-table figures at four scopes. This proves
 * the four numbers a Trainer actually reads are those figures, which is a different failure: the prop
 * can carry `{629, 1325, 0}` and the header can paint placeholder zeroes over it. The
 * `release_status_counts` prop is declared in `SkillCatalog.vue`'s `defineProps` for exactly that
 * reason, and `npm run typecheck` cannot see a missing declaration because `tsc --noEmit` does not
 * read `.vue`.
 *
 * The expected values are not hardcoded. `uma:skill-inventory` groups the same `release_status`
 * column through its own code path, so a controller that counted the wrong column would disagree
 * with it. This is the D6 pattern (`inventoryRangeOpCount`) applied to the header.
 */
test('the header states the full-table provenance counts, unchanged by scope', async ({ page }) => {
    const expected = inventoryReleaseStatusCounts();

    // Guard the cross-check itself: an empty or one-sided scratch table would make every assertion
    // below vacuously true.
    expect(expected.global, 'the scratch catalogue holds Global rows').toBeGreaterThan(0);
    expect(expected.japan_only, 'the scratch catalogue holds JapanOnly rows').toBeGreaterThan(0);

    const readHeader = async (): Promise<{ global: string; japan_only: string; other: string }> => {
        const header = page.getByTestId('provenance-counts');
        await expect(header).toBeVisible();

        return {
            global: (await header.locator('[data-count-kind="global"]').innerText()).trim(),
            japan_only: (await header.locator('[data-count-kind="japan_only"]').innerText()).trim(),
            other: (await header.locator('[data-count-kind="other"]').innerText()).trim(),
        };
    };

    // Default scope: the narrow one. The header still describes the table.
    await page.goto('/skills');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel('Scope')).toHaveValue('global');

    const atGlobal = await readHeader();
    expect(atGlobal.global).toBe(String(expected.global));
    expect(atGlobal.japan_only).toBe(String(expected.japan_only));
    expect(atGlobal.other).toBe(String(expected.other));

    // The paginator states the CURRENT scope's row count, which is why the header does not have to.
    // Two different questions, two different surfaces: the total below is the Global subset, the
    // header's Global figure is every GlobalReleased row in the table.
    await expect(page.locator('nav[aria-label="Pagination"] [role="status"]')).toContainText(
        new RegExp(`of ${expected.global} skills`),
    );

    // Every scope: the same header.
    await page.goto('/skills?scope=all');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel('Scope')).toHaveValue('all');

    const atAll = await readHeader();
    expect(atAll).toEqual(atGlobal);

    // The label and its footnote: `Calculated` is not a bare word. The note states the column and
    // that the figures are scope-invariant, and the label points at it.
    const label = page.locator('[data-testid="provenance-counts"] [data-footnote-id="provenance-source"]');
    await expect(label).toHaveText('Calculated');
    await expect(label).toHaveAttribute('aria-describedby', 'provenance-source');

    const footnote = page.locator('#provenance-source');
    await expect(footnote).toContainText('release_status');
    await expect(footnote).toContainText('do not change with the scope filter');
});
