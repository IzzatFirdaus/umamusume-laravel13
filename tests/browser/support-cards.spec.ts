import { test, expect } from '@playwright/test';

// Rendered-copy evidence for the ported support-card screens (SCR-SUP-001/002, ADR-0020 §1). The
// server-side tests (SupportCardPageTest) assert the resolved props; these assert what the browser
// shows. Runs against the app's own seeded scratch database (playwright.config.ts): card 1 is
// Special Week [Tracen Academy], whose Japanese name and epithet both print, whose char_id resolves
// to the Special Week trainee page, and whose hint and event ids all resolve; card 88 carries one
// hint id no Global skill page answers for; card 21 is a Pal card whose 9000-block char_id has no
// trainee row; card 20's hint list is stored empty.

test('links the Support Cards screen from the primary navigation and marks it current', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    const link = page.locator('aside').getByRole('link', { name: 'Support Cards' });
    await expect(link).toBeVisible();
    await expect(link).toHaveAttribute('aria-current', 'page');
});

test('sizes the four pickers and the submit to the 44px contract', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // KI-29 / DESIGN.md §6.14: the four selects and the submit are 44px tall. The selects are
    // addressed by name rather than by label: a wrapping label's computed name swallows the option
    // text, so `getByLabel('Rarity')` also matches the sort picker's "Rarity" option.
    for (const [name, word] of [['rarity', 'Rarity'], ['type', 'Type'], ['status', 'Availability'], ['sort', 'Sort by']]) {
        await expect(page.locator(`label:has(select[name="${name}"])`)).toContainText(word);

        const box = await page.locator(`select[name="${name}"]`).boundingBox();
        expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Filter' }).boundingBox();
    expect(submit?.height ?? 0, 'the Filter button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
});

test('renders each row with the label, the rarity word and the type word', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText(/\d+ of \d+ support cards/)).toBeVisible();

    // The first row is the alphabetically first card, Admire Groove's R form.
    const firstRow = page.locator('#support-card-results > li').first();
    await expect(firstRow).toContainText('Admire Groove');
    await expect(firstRow.getByRole('img', { name: 'One Star' })).toBeVisible();
    await expect(firstRow.getByText(/^R$/)).toBeVisible();
    await expect(firstRow).toContainText('Speed');
    await expect(firstRow).toContainText('JP-only');
    // The row summarizes the effect at its highest stated anchor, not a level-50 interpolation.
    await expect(firstRow).toContainText('Friendship Bonus 15%');
});

test('keeps the chosen facets selected and names a filter that matched nothing', async ({ page }) => {
    await page.goto('/support-cards?rarity=3&type=guts&status=Global&sort=released');
    await page.locator('#app > *').first().waitFor();

    // The rarity picker is the one that can silently read "All": PHP keys its map on ints while the
    // query string carries text.
    await expect(page.locator('select[name="rarity"]')).toHaveValue('3');
    await expect(page.locator('select[name="type"]')).toHaveValue('guts');
    await expect(page.locator('select[name="status"]')).toHaveValue('Global');
    await expect(page.locator('select[name="sort"]')).toHaveValue('released');

    // A valid facet combination with no rows says so and names the ask rather than reading as an
    // empty catalog. No R-rarity group card exists.
    await page.goto('/support-cards?rarity=1&type=group');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Nothing matches rarity R \+ type group/)).toBeVisible();
});

test('renders one card\'s own page with the fields the source states', async ({ page }) => {
    await page.goto('/support-cards/1');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Special Week [Tracen Academy]', level: 1 })).toBeVisible();

    // WCAG 3.1.2 Language of Parts: the Japanese name and epithet carry lang="ja".
    const japanese = page.locator('main [lang="ja"]');
    await expect(japanese).toContainText('スペシャルウィーク');
    await expect(japanese).toContainText('[トレセン学園]');

    // The field grid states what the source records.
    await expect(page.getByRole('img', { name: 'One Star' })).toBeVisible();
    await expect(page.getByText('Guts', { exact: true })).toBeVisible();
    await expect(page.getByText('Global', { exact: true })).toBeVisible();
    await expect(page.getByText('Feb 24, 2021')).toBeVisible();
    await expect(page.getByText('Jun 26, 2025')).toBeVisible();

    // Belongs to resolves the char_id to the trainee page.
    await expect(page.getByRole('link', { name: 'Special Week' })).toHaveAttribute('href', /\/umamusume\/special-week$/);

    // Effects at cap name their basis, never a level-50 interpolation.
    await expect(page.getByRole('heading', { name: 'Effects', level: 2 })).toBeVisible();
    await expect(page.getByText('Friendship Bonus 15%')).toBeVisible();
    await expect(page.getByText(/highest stated anchor/)).toBeVisible();

    // The two skill lists keep the source's order and link each row to its own page.
    await expect(page.getByRole('heading', { name: 'Hinted skills', level: 2 })).toBeVisible();
    await expect(page.locator('main').getByRole('link', { name: 'Homestretch Haste' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Event skills', level: 2 })).toBeVisible();
    await expect(page.locator('main').getByRole('link', { name: 'Extra Tank' })).toBeVisible();

    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();
    // The seeded snapshot URL carries a content hash, so the host and the document name are asserted.
    await expect(page.getByText(/gametora\.com\/data\/umamusume\/support-cards/)).toBeVisible();
    await expect(page.getByRole('link', { name: 'Back to support cards' })).toBeVisible();
});

test('counts a hint id it cannot link and names a card with no trainee page', async ({ page }) => {
    // Card 88 carries one hint id no Global skill page answers for.
    await page.goto('/support-cards/88');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/1 hinted skill ids in the source's list resolve to no skill page/)).toBeVisible();

    // Card 21 is a Pal card whose 9000-block char_id has no trainee row.
    await page.goto('/support-cards/21');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/has no trainee page in this catalog/)).toBeVisible();
});

test('separates a list the source states as empty from a list nothing stored', async ({ page }) => {
    // Card 20's hint list is stored as empty, which is the source stating a fact, not a missing list.
    await page.goto('/support-cards/20');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText('The source lists no hinted skills for this card.')).toBeVisible();
});
