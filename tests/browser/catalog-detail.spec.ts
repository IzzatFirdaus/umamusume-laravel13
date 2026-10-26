import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

// Rendered-copy evidence for the ported Catalog detail page (SCR-CAT-002, ADR-0020 §1).
// The server-side tests (CatalogDetailPageTest, CatalogSkillListsTest, CatalogRosterTreeTest)
// assert the resolved props; these assert what the browser shows. Runs against the app's own
// scratch database (playwright.config.ts), so the fixtures are seeded rows: `special-week` has
// three costume forms, a profile and a provenance row; `silence-suzuka` has exactly one form.
//
// Not covered here, and it is not coverable by this suite: the positive "Not yet released on Global"
// notice. `UmamusumeRosterSeeder::inScope()` files `JapanOnly` rows as pending candidates rather than
// promoting them (PRD FR-A-1: this is a Global-client tool; measured 68 promoted of 135), so no
// `umamusume` row in any database this app builds can reach that branch, and rewriting the served page
// object in flight does not work either: Blade points at the Vite dev server on `localhost:5173`, and
// Chromium's private-network check refuses those asset requests for a `route.fulfill()`-synthesized
// document, so the page never hydrates. What is proven: the prop that drives the branch
// (`CatalogDetailPageTest`), and that the machine token stays off a real page (the last test below).

// The HFW region order (plan Slice 8, `catalog-detail.html`): values and identity on the left, the
// costume forms and their provenance on the right, then the regions this app answers beyond the HFW.
const SECTIONS = [
    'Base stats and growth',
    'Profile',
    'Aptitudes, full set',
    'Costumes',
    'Star breakthrough',
    'Unique skill',
    'Provenance',
    'Skills on this form',
    'Trainee goals',
    'Her runs',
    'Aliases',
];

test('renders the page in the binding region order', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Special Week', level: 1 })).toBeVisible();

    // The order is the plan's: the HFW regions first, then the ones this app answers beyond it.
    const headings = await page.getByRole('heading', { level: 2 }).allTextContents();
    expect(headings).toEqual(SECTIONS);
});

// Plan 5.4: the trainee's own fetch history is ONE disclosure. The heading stays on screen so the
// section is findable, and the summary says how many sources there are before the disclosure is
// opened, so a reader learns the size of it without opening it.
test('holds her fetch history in one disclosure', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Provenance', level: 2 })).toBeVisible();

    const disclosure = page.locator('#provenance');
    await expect(disclosure).toHaveCount(1);
    await expect(disclosure.locator('summary')).toContainText(/\d+ fetched source/);

    // Provenance is a row plus a link to the ledger, and no file path reaches the screen.
    await expect(page.getByRole('link', { name: 'Source ledger' })).toBeVisible();
    await expect(page.locator('main')).not.toContainText('storage/app');
    await expect(page.locator('main')).not.toContainText('snapshot');
});

// Plan 5.4 (Sweep 1): the ten letters printed as one flat grid, so "can she run Long" meant counting
// letters in a four-column run. Each of the export's three reads now carries its own heading. The
// headings are `h3` under the section's own `h2`, which is why the case above still sees eight.
test('groups the aptitude letters under their own headings', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    const section = page.locator('section[aria-labelledby="aptitudes"]');

    // The three reads in the export's own order: surface, the four distances, the four styles.
    await expect(section.getByRole('heading', { level: 3 })).toHaveText(['Surface', 'Distance', 'Style']);

    // A letter belongs to its own group rather than to the flat run: "Long" is a distance, "Turf" is
    // not, and the grouping is what makes that findable.
    const grids = section.locator('dl');
    await expect(grids).toHaveCount(3);
    await expect(grids.nth(0).getByText('Turf', { exact: true })).toBeVisible();
    await expect(grids.nth(1).getByText('Long', { exact: true })).toBeVisible();
    await expect(grids.nth(1).getByText('Turf', { exact: true })).toHaveCount(0);

    // The new `h3` level must not skip: the section's own heading is the `h2` above it, and the
    // eight-section case above is the assertion that no `h2` moved. `buildAxe` scopes to `#app`
    // (KI-63).
    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

// ADR-0026's widest-reach half (owner decision 3): the trainee's own objectives, readable before a run
// exists. The composition is proven server-side by `CatalogDetailPageTest` (order, the two-query read,
// the row-for-row match with the Cockpit, the promotion freshness); what only a browser can see is that
// the page renders the ladder rather than the retired absence sentence, and that the gap on an unread
// requirement is a disclosure a Trainer can open.
//
// The empty branch is not coverable here, and the reason is the same one recorded above for the "Not
// yet released on Global" notice: the suite's scratch database is built by `migrate --seed`, which
// promotes 67 trainees and gives every one of them a ladder (measured 2026-10-14: 829 rows, 67
// distinct trainees, zero promoted trainees with none). The empty prop is proven in the feature test.
test('prints her own ladder, the source wording, and the reason a rung is unread', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    // Scoped to the block that owns the rows. A page-wide `getByText` that matches a race name printed
    // in another section is the false pass that bit the scenario panel specs.
    const goals = page.locator('section[aria-labelledby="trainee-goals"]');

    const rows = goals.getByRole('listitem');
    // Special Week's ladder on the seeded document: 10 stored rows, 3 of them the post-December rounds
    // the Mandatory races group renders, so 7 rungs print here.
    await expect(rows).toHaveCount(7);

    await expect(rows.first()).toContainText('Participate in Junior Make Debut');
    // The clock the whole page shares: the turn counted within its own career year, not the source's
    // absolute turn 12-within-78.
    await expect(rows.first()).toContainText('Junior year, turn 12');
    await expect(rows.last()).toContainText('Place 1st in Arima Kinen');

    // One rung this tool has not decoded (KI-100): the row keeps the race the calendar names, and the
    // gap is an N/A whose disclosure states the source's own condition codes rather than a guess.
    const unread = goals.locator('summary', { hasText: 'has not decoded' });
    await expect(unread).toHaveCount(1);
    await expect(unread).toContainText('N/A');

    // No progress readout where there is no run to read one from, and the block says where it lives.
    await expect(goals.getByText('Goals Achieved')).toHaveCount(0);
    await expect(goals).toContainText('they print in the Cockpit while a career is in progress');

    // The sentence this block replaced is gone, not merely behind a different heading.
    await expect(goals).not.toContainText('not recorded');

    // No em dash in shipped copy (AGENTS.md §5).
    await expect(goals).not.toContainText('—');

    // X-3, one accented primary per screen: the ladder adds no control, so the "New run" button stays
    // the only primary-action element on the page, as it was before this block rendered rows.
    await expect(page.locator('.bg-primary-action')).toHaveCount(1);
});

test('prints the Japanese name in its own language and links the provenance source', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    // WCAG 3.1.2 Language of Parts: the Japanese name carries lang="ja" so a screen reader
    // switches voice rather than reading the kana as English.
    await expect(page.locator('[lang="ja"]', { hasText: 'スペシャルウィーク' }).first()).toBeVisible();

    // The trainee's own fetch history is a real link, inside the provenance disclosure (the section
    // folds its source list; the summary states the count before it opens).
    await page.locator('details#provenance summary').click();
    await expect(page.getByRole('link', { name: /character-cards/ }).first()).toBeVisible();

    // The voice actor's own Japanese name is a language part too (WCAG 3.1.2): without lang="ja"
    // a screen reader reads the kana as English.
    const voiceActor = page.getByText('Voice actor').locator('xpath=following-sibling::td');
    await expect(voiceActor.locator('[lang="ja"]')).toHaveCount(1);

    // The handoff names the trainee it starts, so the button says whose career it begins.
    await expect(page.getByRole('button', { name: /Start career with Special Week/ })).toBeVisible();
});

test('switches costume forms from the tab strip with the keyboard', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    const tabs = page.getByRole('navigation', { name: 'Costume forms' }).getByRole('link');
    await expect(tabs).toHaveCount(3);

    // The active form is announced, not just styled (WCAG 1.3.1 / 4.1.2).
    await expect(tabs.first()).toHaveAttribute('aria-current', 'page');

    // Each tab is a 44px target, the house bar over WCAG 2.5.8's 24px floor.
    const heights = await tabs.evaluateAll((els) => els.map((el) => el.getBoundingClientRect().height));
    for (const height of heights) {
        expect(height).toBeGreaterThanOrEqual(44);
    }

    // Native links, so Tab reaches them and Enter follows them: the defect the port replaced was
    // a CSS-only radio group whose panels never left `hidden` and needed a submit to address.
    const second = tabs.nth(1);
    const secondTitle = ((await second.textContent()) ?? '').trim();

    await second.focus();
    await page.keyboard.press('Enter');

    await expect(page).toHaveURL(/form=\d+/);
    await expect(second).toHaveAttribute('aria-current', 'page');
    await expect(tabs.first()).not.toHaveAttribute('aria-current', 'page');
    // The panel switched to the form the tab names.
    await expect(page.getByRole('heading', { name: secondTitle, level: 3 })).toBeVisible();
});

test('renders no tab strip for a trainee with a single form', async ({ page }) => {
    await page.goto('/umamusume/silence-suzuka');
    await page.locator('#app > *').first().waitFor();

    // One form is not a choice, so there is no strip and no radio group: no chrome, not less page.
    await expect(page.getByRole('navigation', { name: 'Costume forms' })).toHaveCount(0);
    await expect(page.locator('input[type="radio"]')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Costumes', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: '[Innocent Silence]', level: 3 })).toBeVisible();
});

test('prints no release-status machine token and no notice for a Global trainee', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    // The client word prints; the enum's backing value never reaches the page (EnumLabelTest
    // pins the prop, this pins the rendered DOM).
    const html = await page.content();
    expect(html).not.toContain('JapanOnly');

    await expect(page.getByText(/Not yet released on Global/)).toHaveCount(0);
});

// The `ADR-0021` Identity slot on the ported catalog detail page (`design-2.0` §45a "Catalog detail,
// Identity"). The mirror ships empty and nothing in this suite runs `uma:fetch-art`, so each case
// derives its state from the rendered DOM and asserts only when that state is present, the same
// `:36` rarity-chip precedent the catalog index uses. The props are proven server-side by
// `CatalogDetailArtworkSlotTest`; what only a browser can see is the geometry, the alt, and the
// anchor's absence.
test('renders the Identity portrait as a bare decorative frame when the mirror holds it', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    const frame = page.locator('section[aria-labelledby="profile"] img').first();
    if (await frame.isVisible().catch(() => false)) {
        // Decorative: the shell banner's `<h1>` is this trainee's name, so it is read once from the
        // text (`design-2.0` §42).
        await expect(frame).toHaveAttribute('alt', '');
        await expect(frame).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);

        // §45a records `size-16`, so the painted box is the reserved square rather than a collapsed
        // or auto-sized image.
        const box = await frame.boundingBox();
        expect(box?.width ?? 0, 'the Identity frame has no rendered width').toBeGreaterThan(0);
        expect(box?.height ?? 0, 'the Identity frame has no rendered height').toBeGreaterThan(0);
    }

    // The assertion that matters most on this screen, and the one the Blade component could not
    // satisfy: §45a gives the detail slot the click action *no action*, so there must be no anchor
    // anywhere around it. The Blade version always emitted one, producing a link to the page already
    // open whose `decorative` flag had just blanked the `aria-label` — an unnamed focusable control,
    // which fails WCAG 2.2 AA 4.1.2. A frame with no name and no purpose must not be focusable at all.
    await expect(page.locator('section[aria-labelledby="profile"] a img')).toHaveCount(0);
    await expect(page.locator('section[aria-labelledby="profile"] a[aria-label=""]')).toHaveCount(0);
});

test('leaves the Identity section text-only when the mirror holds no file', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    const section = page.locator('section[aria-labelledby="profile"]');
    const frame = section.locator('img');

    // An empty `src` is the one shape §4.7 never allows, and it is a fact about this screen rather
    // than about the disk, so it is asserted whatever the mirror holds.
    await expect(page.locator('img[src=""]')).toHaveCount(0);

    // Whether this trainee has a frame is a property of the disk, not of the screen: `uma:fetch-art
    // --dry-run` reports 106 of 106 `card_portrait` ids already on disk on this tree, so the case may
    // not assume either state (`AGENTS.md` §18's empty-mirror note, from the other side;
    // `support-deck.spec.ts` settled the same question for the picker's thumbnails). Each branch
    // therefore asserts its own shape rather than skipping, and the absent-file path itself is proven
    // server-side by `CatalogDetailArtworkSlotTest`.
    if ((await frame.count()) === 0) {
        // Absence is a normal state (`DESIGN.md` §4.7): no frame, and nothing that would draw a box
        // where one could have been.
        await expect(section.locator('img')).toHaveCount(0);
    } else {
        // A populated mirror takes the other branch: the frame is decorative and carries no anchor,
        // which is the defect the port replaced (an unnamed focusable link around a blanked image).
        await expect(frame.first()).toHaveAttribute('alt', '');
        await expect(frame.first()).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);
        await expect(section.locator('a img')).toHaveCount(0);
    }

    // And the section still reads: the heading and the page name are what the screen is for.
    await expect(page.getByRole('heading', { name: 'Profile' })).toBeVisible();
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
});
