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

    await expect(page).toHaveURL(/\/skills\/\d+$/);
    await expect(page.getByRole('heading', { name: '#LookatCurren', level: 1 })).toBeVisible();

    // The two name columns agree on this row, so the pair prints once: no second slot.
    await expect(page.locator('main [lang="ja"]')).toHaveCount(0);

    // D-256: the derived badge prints its rule and whose derivation it is.
    const badge = page.locator(`[title="${UNIQUE_BADGE_DISCLOSURE}"]`);
    await expect(badge).toBeVisible();
    await expect(badge).toHaveText('✦ Unique');

    // D-220: an absent SP cost renders N/A with a title naming the kind of absence.
    const spCost = page.locator(`[title="${NO_SP_COST_TITLE}"]`);
    await expect(spCost).toBeVisible();
    await expect(spCost).toHaveText('N/A');

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
    await expect(page).toHaveURL(/\/skills\/\d+$/);
    await page.locator('#app > *').first().waitFor();

    // The condition table is the primary content, so it is not behind a disclosure: a fold would
    // make the one representation that can be diffed against the export the second thing read.
    const mechanics = page.locator('section[aria-labelledby="mechanics"]');
    await expect(mechanics.locator('details')).toHaveCount(0);
    await expect(mechanics).toContainText('Condition');
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
    await expect(page).toHaveURL(/\/skills\/\d+$/);
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

    await expect(page.getByLabel('Distance'), 'a Distance facet the catalogue cannot answer').toHaveCount(0);

    const table = page.locator('#skill-results');
    const card = table.locator('xpath=ancestor::div[contains(@class,"overflow-x-auto")]');
    const note = card.getByText(/The condition column prints the export's own tokens/);
    await expect(note).toBeVisible();

    const noteBottom = await note.evaluate((node) => node.getBoundingClientRect().bottom);
    const pagerTop = await page.locator('nav[aria-label="Pagination"]').evaluate((node) => node.getBoundingClientRect().top);
    expect(noteBottom, 'the note sits above the pager').toBeLessThanOrEqual(pagerTop);
});

test('shows absence labels only for missing skill values', async ({ page }) => {
    const response = await page.goto('/skills');
    expect(response?.ok()).toBe(true);
    await expect(page.locator('#skill-results tr')).not.toHaveCount(0);

    const populated = page.locator('#skill-results tr');
    let populatedCells = 0;
    for (const row of await populated.all()) {
        for (const column of [1, 2, 3]) {
            const cell = row.locator('td').nth(column);
            const value = (await cell.innerText()).trim();
            if (value.startsWith('N/A')) {
                await expect(cell.locator('[title]')).toHaveCount(1);
                continue;
            }
            populatedCells++;
            await expect(cell).not.toContainText('N/A');
        }
    }
    expect(populatedCells).toBeGreaterThan(0);

    await page.goto('/skills?search=lookatcurren');
    const missingCost = page.locator(`#skill-results [title="${NO_SP_COST_TITLE}"]`);
    await expect(missingCost).toBeVisible();
    await expect(missingCost).toHaveText('N/A');
});

test('the hint ladder is a reference and the base cost never prints its absence beside a value', async ({ page }) => {
    // The wireframe's ladder row (skill-detail.html, keybox 3). ADR-0011/ADR-0029 store no hint
    // level, so the page prints the published ladder as a reference and derives no per-skill price.
    await page.goto('/skills/1');
    await page.locator('#app > *').first().waitFor();

    const ladder = page
        .getByRole('heading', { name: 'Displayed cost at each hint level' })
        .locator('xpath=following::table[1]');
    await expect(ladder).toBeVisible();
    await expect(ladder.getByRole('row')).toHaveCount(6);
    await expect(ladder).toContainText('Lv1');
    await expect(ladder).toContainText('Lv Max');
    await expect(ladder).toContainText('40%');
    // A reference, not a price: no SP figure is derived from the ladder.
    await expect(ladder).not.toContainText('SP');

    // A value and its absence are mutually exclusive in the Base-cost cell. Before the fix the
    // cell printed "180 SP" and the N/A twin at once because the absence span had no v-else.
    const costCell = page
        .getByRole('rowheader', { name: 'Base cost' })
        .locator('xpath=following-sibling::td[1]');
    await expect(costCell).toContainText('SP');
    await expect(costCell).not.toContainText('N/A');

    // And the absent case still names the absence, on the skill whose cost is null.
    await page.goto('/skills/370');
    await page.locator('#app > *').first().waitFor();
    const absentCell = page
        .getByRole('rowheader', { name: 'Base cost' })
        .locator('xpath=following-sibling::td[1]');
    await expect(absentCell).toContainText('N/A');
    // Its sr-only reason names the SP cost, so the absence assertion is "no numeric SP figure".
    await expect(absentCell).not.toContainText(/\d+ SP/);
});
