import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

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

test('sizes the search box, the four pickers and the submit to the 44px contract', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // KI-29 / DESIGN.md §6.14: the search box, the four selects and the submit are 44px tall. The
    // selects are addressed by name rather than by label: the sort picker offers a "Rarity" option and
    // a "Type" option, so a label query for those words is ambiguous. The label is then found through
    // the `for` association the select carries, because `BaseSelect` renders the label BESIDE the
    // select: the old `label:has(select[name=...])` selector assumed a wrapping label and matched
    // nothing, which is why this case was red on the tree before this slice.
    const search = await page.getByLabel('Search').boundingBox();
    expect(search?.height ?? 0, 'the search box is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    for (const [name, word] of [['rarity', 'Rarity'], ['type', 'Type'], ['server', 'Server'], ['sort', 'Sort by']]) {
        const select = page.locator(`select[name="${name}"]`);
        const id = await select.getAttribute('id');
        await expect(page.locator(`label[for="${id}"]`)).toContainText(word);

        const box = await select.boundingBox();
        expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Filter' }).boundingBox();
    expect(submit?.height ?? 0, 'the Filter button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
});

test('renders each row with the label, the rarity word and the type word', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // The wireframe's scope line (`support-cards.html:148`): the list is one server's, defaulting
    // to Global, so the count names that server rather than every row the export holds.
    await expect(page.getByText(/\d+ cards on \[Global\]/)).toBeVisible();

    // The first row is the alphabetically first card the Global scope holds. Admire Groove's forms
    // are JP-only (`release_global` null) and so are out of this list by construction, which is the
    // scope line asserted above doing its job; Admire Vega's `[Aim for the Brightest]` is the first
    // Global row by `char_name`, then `title_en`.
    const firstRow = page.locator('#support-card-results > tr').first();
    await expect(firstRow).toContainText('Admire Vega');
    await expect(firstRow).toContainText('Aim for the Brightest');
    await expect(firstRow.getByRole('img', { name: 'Two Star' })).toBeVisible();
    await expect(firstRow.getByText(/^SR$/)).toBeVisible();
    await expect(firstRow).toContainText('Guts');
    // The row summarizes the effect at its highest stated anchor, not a level-50 interpolation.
    await expect(firstRow).toContainText('Friendship Bonus 30%');
});

test('keeps the chosen facets selected and names a filter that matched nothing', async ({ page }) => {
    await page.goto('/support-cards?rarity=3&type=guts&server=Global&sort=released');
    await page.locator('#app > *').first().waitFor();

    // The rarity picker is the one that can silently read "All": PHP keys its map on ints while the
    // query string carries text.
    await expect(page.locator('select[name="rarity"]')).toHaveValue('3');
    await expect(page.locator('select[name="type"]')).toHaveValue('guts');
    await expect(page.locator('select[name="server"]')).toHaveValue('Global');
    await expect(page.locator('select[name="sort"]')).toHaveValue('released');

    // A valid facet combination with no rows says so and names the ask rather than reading as an
    // empty catalog. No R-rarity group card exists.
    await page.goto('/support-cards?rarity=1&type=group');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Nothing matches rarity R \+ type group/)).toBeVisible();
});

test('puts the name search first, matches a printed label, and shows five rows', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // The wireframe's filter row starts with the name search (`support-cards.html:72-73`), and its
    // placeholder is the field's own subject.
    const form = page.locator('form').filter({ has: page.getByRole('button', { name: 'Filter' }) });
    await expect(form.locator('label').first()).toContainText('Search');
    await expect(page.getByLabel('Search')).toHaveAttribute('placeholder', 'Card name');

    // Five rows: the wireframe's page size, not the catalogue's old 25.
    await expect(page.locator('#support-card-results > tr')).toHaveCount(5);

    await page.getByLabel('Search').fill('Special Week');
    await page.getByRole('button', { name: 'Filter' }).click();
    await expect(page).toHaveURL(/q=Special/);

    const rows = page.locator('#support-card-results > tr');
    const matched = await rows.count();
    expect(matched, 'the name search returned nothing').toBeGreaterThan(0);
    expect(matched, 'one page cannot hold more than five rows').toBeLessThanOrEqual(5);

    for (let index = 0; index < matched; index += 1) {
        await expect(rows.nth(index), `row ${index} does not carry the searched name`).toContainText('Special Week');
    }

    // A search that matches nothing names the ask rather than reading as an empty catalogue.
    await page.goto('/support-cards?q=nothing-matches-this');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Nothing matches the name "nothing-matches-this"/)).toBeVisible();
});

test('states the seven types by rarity and the five effects that decide a deck slot', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    const types = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'The seven types' }) });
    await expect(types.getByRole('columnheader', { name: 'JP R/SR/SSR' })).toBeVisible();
    await expect(types.getByRole('columnheader', { name: 'Global SSR' })).toBeVisible();
    await expect(types.locator('tbody tr')).toHaveCount(7);
    await expect(types.locator('tbody tr').first()).toContainText('Speed');
    await expect(types.locator('tbody tr').first()).toContainText(/\d+ \/ \d+ \/ \d+/);

    // The wireframe's five deciding effects, by the export's own names (`support-cards.html:170-179`).
    const effects = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Effects that decide a deck slot' }) });
    await expect(effects.locator('tbody tr')).toHaveCount(5);

    for (const name of ['Friendship Bonus', 'Initial Friendship Gauge', 'Specialty Priority', 'Failure Protection', 'Wit Friendship Recovery']) {
        await expect(effects.getByRole('cell', { name, exact: true }), `${name} is not a row`).toBeVisible();
    }

    // The card it replaced spoke about vocabulary no card carries; that fact is now the closing note.
    await expect(page.getByRole('heading', { name: 'Effects no card carries' })).toHaveCount(0);
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
    const friendshipRow = page.locator('tr', { hasText: 'Friendship Bonus' });
    await expect(friendshipRow).toContainText('1: 5%');
    await expect(friendshipRow).toContainText('15%');
    await expect(page.getByText(/highest stated anchor/)).toBeVisible();

    // The two skill lists keep the source's order and link each row to its own page.
    await expect(page.getByRole('heading', { name: 'Hinted skills', level: 2 })).toBeVisible();
    await expect(page.locator('main').getByRole('link', { name: 'Homestretch Haste' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Event skills', level: 2 })).toBeVisible();
    await expect(page.locator('main').getByRole('link', { name: 'Extra Tank' })).toBeVisible();

    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();
    // The seeded snapshot URL carries a content hash, so the host and the document name are asserted.
    // The provenance is ONE disclosure now, so the source is read from inside it.
    await page.locator('#provenance summary').click();
    await expect(page.getByText(/gametora\.com\/data\/umamusume\/support-cards/)).toBeVisible();
    // The HFW's own label for the way back ("support-card-detail.html:65").
    await expect(page.getByRole('link', { name: 'Back to cards' })).toBeVisible();
});

test('holds the row provenance in one disclosure that names its state', async ({ page }) => {
    await page.goto('/support-cards/1');
    await page.locator('#app > *').first().waitFor();

    // The heading stays on screen so the section is findable, and the summary says which state the
    // row is in before the disclosure is opened.
    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();

    const disclosure = page.locator('#provenance');
    await expect(disclosure).toHaveCount(1);
    await expect(disclosure.locator('summary')).toContainText('Fetched source');
});

test('the support card index states the page it is showing', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // The canonical pager states the range and the page. The pager it replaced was a set of links
    // and, on a short list, rendered nothing at all.
    const status = page.locator('nav[aria-label="Pagination"] [role="status"]');
    await expect(status).toContainText(/Showing \d+ to \d+ of \d+ cards/);
    await expect(status).toContainText(/Page 1 of \d+/);
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

// The `ADR-0021` thumbnail slots on the two support-card screens (`design-2.0` §45a "Support-card
// index, card row" and "Support-card detail, header"). Whether the mirror holds files is a property
// of the host, not of the suite — a tree that has run `uma:fetch-art` holds all of them, a fresh one
// holds none — so each case derives its state from the rendered DOM and asserts the shape of
// whichever state is present, following the catalog index spec's `:36` rarity-chip precedent and
// plan §4.1 item 5's standing ruling (KI-69 class 2). The props, including the `support_id`-versus-
// local-`id` discipline, are proven server-side by `SupportCardsArtworkSlotTest`.
test('renders a mirrored row thumbnail as a named link to the same card', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    const framed = page.locator('#support-card-results > tr:has(img)').first();
    if (await framed.locator('img').isVisible().catch(() => false)) {
        const frame = framed.locator('img');
        // Decorative: the row prints the card's name beside the frame, so the name is read once.
        await expect(frame).toHaveAttribute('alt', '');
        await expect(frame).toHaveAttribute('src', /\/artwork\/support_thumb\/\d+$/);

        // §45a records `size-12` for this row, so the box is the reserved square.
        const box = await frame.boundingBox();
        expect(box?.width ?? 0, 'the row thumbnail has no rendered width').toBeGreaterThan(0);
        expect(box?.height ?? 0, 'the row thumbnail has no rendered height').toBeGreaterThan(0);

        // This is the one slot of the five that is genuinely clickable, so it is also the one where
        // an accessible name is mandatory rather than optional. `alt=""` leaves the anchor unnamed,
        // and an unnamed link fails WCAG 2.2 AA 4.1.2; `aria-label` has to carry the row's printed
        // name verbatim, which is also what makes WCAG 2.5.3 label-in-name hold (§42).
        const link = framed.locator('a:has(img)');
        await expect(link).toHaveCount(1);
        const label = await link.getAttribute('aria-label');
        const rowName = (await framed.locator('a').first().innerText()).trim();
        expect(label, 'the thumbnail link carries no accessible name').toBeTruthy();
        expect(label).toContain(rowName);

        // The destination is the row's own link target, the local primary key, while the frame is
        // keyed on the publisher's `support_id`. Same destination, and the row keeps its name link.
        const href = await link.getAttribute('href');
        const nameHref = await framed.locator('a').first().getAttribute('href');
        expect(href).toBe(nameHref);
    }
});

test('leaves support-card rows text-only when the mirror holds no file, and decorative when it does', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    // An empty `src` is the one shape `DESIGN.md` §4.7 never allows, and it is a fact about this
    // screen rather than about the disk, so it is asserted whatever the mirror holds.
    await expect(page.locator('#support-card-results img[src=""]')).toHaveCount(0);

    // Whether a frame exists at all is a property of the disk (`uma:fetch-art --dry-run` reports
    // every `support_thumb` id already on disk on this tree), so the case may not assume either
    // state: each branch asserts its own shape, and the populated branch's full contract is the
    // sibling case above.
    const framed = page.locator('#support-card-results > tr:has(img)').first();

    if ((await framed.count()) === 0) {
        // Absence is a normal state (§4.7): no frame anywhere, and no clickable frame-shaped hole.
        await expect(page.locator('#support-card-results img')).toHaveCount(0);
        await expect(page.locator('#support-card-results a:has(img)')).toHaveCount(0);
    } else {
        // A populated mirror takes the other branch: the frame is decorative and framed by the
        // row's named link, which is the contract the sibling case reads in full.
        await expect(framed.locator('img').first()).toHaveAttribute('alt', '');
        await expect(framed.locator('a:has(img)')).toHaveCount(1);
    }

    // The rows still read as before.
    await expect(page.locator('#support-card-results > tr').first().locator('a').first()).toBeVisible();
});

test('renders the support-card header thumbnail as a bare frame with no link', async ({ page }) => {
    await page.goto('/support-cards/1');
    await page.locator('#app > *').first().waitFor();

    const frame = page.locator('img[src*="/artwork/support_thumb/"]').first();
    if (await frame.isVisible().catch(() => false)) {
        await expect(frame).toHaveAttribute('alt', '');
        const box = await frame.boundingBox();
        expect(box?.width ?? 0, 'the header thumbnail has no rendered width').toBeGreaterThan(0);
        expect(box?.height ?? 0, 'the header thumbnail has no rendered height').toBeGreaterThan(0);
    }

    // §45a gives this slot *no action*, so the header frame must not be a link. The Blade component
    // always emitted one, which on a detail page meant a link to the page already open, with its
    // `aria-label` blanked by the same flag that blanked the alt: a nameless focus target, failing
    // WCAG 2.2 AA 4.1.2. Asserted unconditionally because it must hold whether or not art is present.
    await expect(page.locator('a:has(img[src*="/artwork/support_thumb/"])')).toHaveCount(0);
    await expect(page.locator('a[aria-label=""]')).toHaveCount(0);
});

/*
 * The support-card filter's busy state: the same idiom the roster and skills filters use, read off
 * the live element while a visit is held open.
 */
test('the support-card filter holds its pending state while the filtered page is in flight', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    let release: (() => void) | undefined;
    const held = new Promise<void>((resolve) => {
        release = resolve;
    });

    await page.route('**/support-cards*', async (route) => {
        await held;
        await route.continue();
    });

    const filter = page.getByRole('button', { name: 'Filter' });
    const form = page.locator('form').filter({ has: filter });

    await filter.click();

    await expect(form, 'the form says it is busy while the visit is in flight').toHaveAttribute('aria-busy', 'true');
    await expect(filter, 'the submit is disabled while the visit is in flight').toBeDisabled();

    release?.();

    await expect(form, 'the busy state clears when the visit finishes').not.toHaveAttribute('aria-busy', 'true');
});

/*
 * The support-card filter's error state. `SupportCardSearchRequest` validates the four facets
 * against fixed lists, so the surface is a URL rather than the dropdown, and the message has to
 * land on the facet that failed.
 */
test('the support-card filter shows a rejected rarity on the field that failed', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    const referer = page.url();
    await page.setExtraHTTPHeaders({ Referer: referer });
    await page.goto('/support-cards?rarity=not-a-rarity');
    await page.locator('#app > *').first().waitFor();

    const rarity = page.locator('select[name="rarity"]');

    await expect(rarity, 'the rejected field is marked invalid').toHaveAttribute('aria-invalid', 'true');
    await expect(
        page.locator('#support-card-rarity-error'),
        'the error renders where the field can point at it',
    ).toBeVisible();
    await expect(rarity, 'the field points at its own error').toHaveAttribute(
        'aria-describedby',
        'support-card-rarity-error',
    );
});

/*
 * The support-card index's own axe scan (plan slice 8.8b).
 *
 * `/support-cards` is a route of its own with a page of its own, and the Database hub's
 * `/database/supports` is a different page: a scan on one says nothing about the other.
 */
test('the support-card index passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * The support-card DETAIL page's own axe scan (plan slice 8.8c).
 *
 * `/support-cards/{card}` is a route of its own, and the corpus reaches it by literal navigation but
 * never scans it: the index page's scan says nothing about the detail page's markup.
 */
test('the support-card detail passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/support-cards/1');
    await page.locator('#app > *').first().waitFor();

    // The heading is asserted first so a wrong id fails as a missing page rather than passing as
    // a clean scan of whatever error page the route fell back to.
    await expect(page.getByRole('heading', { name: 'Special Week [Tracen Academy]', level: 1 })).toBeVisible();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
