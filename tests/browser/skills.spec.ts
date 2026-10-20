import { test, expect } from '@playwright/test';

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
    for (const name of ['Search', 'Type']) {
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

    // The page's own sections and its way back.
    await expect(page.getByRole('heading', { name: 'Mechanics', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Held by trainees', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();
    // Both mechanics branches name the ruling that keeps the description absent.
    await expect(page.getByText(/ADR-0011/)).toBeVisible();
    await expect(page.getByRole('link', { name: 'Back to skill search' })).toBeVisible();
});

test('the skill detail holds the engine data in one labelled disclosure', async ({ page }) => {
    await page.goto('/skills?search=lookatcurren');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();
    await expect(page).toHaveURL(/\/skills\/\d+$/);
    await page.locator('#app > *').first().waitFor();

    // ONE disclosure for the whole raw block, and its summary states what it holds and whose data
    // it is, so the reader knows that before opening it.
    const disclosure = page.locator('section[aria-labelledby="mechanics"] details');
    await expect(disclosure).toHaveCount(1);
    await expect(disclosure.locator('summary')).toContainText('Raw engine data');
    await expect(disclosure.locator('summary')).toContainText('ADR-0011');

    // The ruling that keeps the description absent is on the summary, not only inside the fold.
    await expect(page.getByText(/ADR-0011/)).toBeVisible();
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
