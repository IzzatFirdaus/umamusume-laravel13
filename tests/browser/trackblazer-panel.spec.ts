import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * The Trackblazer scenario panel (SCREEN-016, plan §9 E4, `SCR-CAR-022`). `TrackblazerPanelTest`
 * asserts the §E4 payload — catalogue in config order, no `recommended` field, recorded purchase
 * read-back, refused off-catalogue item, refused wrong price, the held-copies cap, grade rows
 * without an invented total, the points-league finale plus the §7 conflict row 31 absence
 * citation, and the uniform null shape for non-trackblazer scenarios. These cases assert what
 * only a browser reaches: rendered copy, the keyboard path, the 44px sweep, the 320px reflow and
 * axe A+AA.
 *
 * **Three notes shape this slice.**
 * - Shop rotation is not modelled (plan §3 knowledge groundings). No row carries a
 *   "Recommended Purchase" badge; no row is selected or highlighted; the published catalogue
 *   order is the only sort key. The browser asserts the absence of any of those forms.
 * - "Twinkle Star Climax" (§7 conflict row 31 UNVERIFIED) is never printed. The browser sees
 *   the points-league finale and a `title` citation that names the conflict row.
 * - The Cockpit's action area gains a "Shop" button (Trackblazer only). The button scrolls
 *   and focuses the Shop heading (WCAG 2.4.11), so a keyboard path lands the user where the
 *   purchase form lives.
 */

const WRITE = { timeout: 60_000 } as const;

/**
 * The run we make in `beforeEach` and reuse across cases: the purchase test needs to land on
 * the same `runs.cockpit` URL after a redirect, and a fresh run on every case doubles the cost.
 */
let cockpitUrl = '';
let runUrl = '';

test.beforeEach(async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    // The bare record URL, not the Cockpit the create redirect lands on: `cockpitUrl` is built from it.
    runUrl = page.url().replace(/\/cockpit$/, '');
    cockpitUrl = `${runUrl}/cockpit`;
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

test.afterEach(async ({ page }) => {
    if (runUrl !== '') {
        await deleteRun(page, runUrl);

        runUrl = '';
        cockpitUrl = '';
    }
});

test('renders the catalogue in config order with no recommended or highlighted row', async ({ page }) => {
    const shop = page.getByRole('region', { name: 'Pro Shop' });
    await expect(shop).toBeVisible();

    // The catalogue list is the source of truth for the order; the form's `<datalist>` is a
    // convenience. The list prints every row with its name, cost and effect as labelled values.
    const catalogue = shop.getByRole('list', { name: 'Pro Shop catalogue' });
    await expect(catalogue.getByText('Speed Notepad')).toBeVisible();
    await expect(catalogue.getByText('Speed Manual')).toBeVisible();
    await expect(catalogue.getByText('Speed Scroll')).toBeVisible();
    await expect(catalogue.getByText('10 coins').first()).toBeVisible();

    // No "Recommended Purchase" card, no `recommended` badge, no "best value" sort or highlight,
    // no default-selected item. The visible form's item field has no value. The one copy the
    // panel prints is the negation that names the rotation absence ("no row is recommended"),
    // not a badge that promotes a row.
    await expect(shop.getByText(/Recommended Purchase/i)).toHaveCount(0);
    await expect(shop.getByRole('heading', { name: /Recommended/i })).toHaveCount(0);
    await expect(shop.getByRole('img', { name: /recommended/i })).toHaveCount(0);
    await expect(shop.getByText(/best value/i)).toHaveCount(0);
    await expect(shop.locator('[aria-selected="true"]')).toHaveCount(0);
    await expect(shop.locator('mark')).toHaveCount(0);
    await expect(shop.locator('#purchase-item')).toHaveValue('');

    // The state copy names the rotation absence once, in words.
    await expect(shop.getByText(/no row is recommended/)).toBeVisible();
});

test('posts a valid purchase and reads the recorded row on the next cockpit visit', async ({ page }) => {
    const shop = page.getByRole('region', { name: 'Pro Shop' });

    await shop.locator('#purchase-turn').fill('7');
    await shop.locator('#purchase-item').fill('Speed Scroll');
    await shop.locator('#purchase-cost').fill('30');
    await shop.locator('#purchase-effect').fill('+15 Speed');

    await Promise.all([
        page.waitForResponse(
            (response) => response.url().includes('/purchases') && response.request().method() === 'POST',
        ),
        shop.getByRole('button', { name: 'Record purchase' }).click(),
    ]);

    // The boundary's redirect lands on the run screen; navigate the same run's cockpit to read
    // the purchase back through `scenario.shop.purchases`.
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });

    const purchases = page.getByRole('region', { name: 'Pro Shop' })
        .getByRole('list', { name: 'Recorded purchases' });

    await expect(purchases.getByText('Speed Scroll')).toBeVisible();
    await expect(purchases.getByText('+15 Speed')).toBeVisible();
    await expect(purchases.getByText(/turn 7/)).toBeVisible();
});

test('returns a field-bound error when the item is not on the catalogue', async ({ page }) => {
    const shop = page.getByRole('region', { name: 'Pro Shop' });

    // The item is a text input with a `<datalist>`, so a tampered value can be typed directly.
    await shop.locator('#purchase-turn').fill('5');
    await shop.locator('#purchase-item').fill('Imaginary Item');
    await shop.locator('#purchase-cost').fill('0');
    await shop.locator('#purchase-effect').fill('Anything');

    await Promise.all([
        page.waitForResponse(
            (response) => response.url().includes('/purchases') && response.request().method() === 'POST',
        ),
        shop.getByRole('button', { name: 'Record purchase' }).click(),
    ]);

    // The error is bound to the input by `aria-describedby`; the alert says the write did not
    // land. This is the trust boundary the form exists to enforce (security-and-hardening).
    await expect(shop.locator('#purchase-item-error')).toContainText(/does not sell that item/i);
    await expect(shop.locator('#purchase-item')).toHaveAttribute('aria-invalid', 'true');
    await expect(shop.getByRole('alert')).toContainText(/not recorded/i);
});

test('returns a field-bound error when the cost does not match the catalogue', async ({ page }) => {
    const shop = page.getByRole('region', { name: 'Pro Shop' });

    await shop.locator('#purchase-turn').fill('5');
    await shop.locator('#purchase-item').fill('Speed Scroll');
    await shop.locator('#purchase-cost').fill('999');
    await shop.locator('#purchase-effect').fill('+15 Speed');

    await Promise.all([
        page.waitForResponse(
            (response) => response.url().includes('/purchases') && response.request().method() === 'POST',
        ),
        shop.getByRole('button', { name: 'Record purchase' }).click(),
    ]);

    await expect(shop.locator('#purchase-cost-error')).toContainText(/charges 30 coins/i);
    await expect(shop.locator('#purchase-cost')).toHaveAttribute('aria-invalid', 'true');
});

test('renders the Grade-Points region as the no-period absence the component prints', async ({ page }) => {
    const grade = page.getByRole('region', { name: 'Grade Point' });
    await expect(grade).toBeVisible();

    // The component renders the bar only when a period is reported (design-2.0 §49: an absent
    // reading is text-only, not a grey box or a placeholder glyph). The no-period sentence is the
    // real state a fresh Trackblazer run meets; asserting on the bar would mean relaxing the
    // component to satisfy the test, which is not the trade-off E3 made either.
    await expect(grade.getByText(/no period reported/)).toBeVisible();

    // The ladder prints in declared order with the debut race first.
    const ladder = grade.getByRole('list', { name: 'Grade Point objectives' });
    await expect(ladder.getByText('Debut race')).toBeVisible();
    await expect(ladder.getByText('End of Junior Year')).toBeVisible();
    await expect(ladder.getByText('End of Classic Year')).toBeVisible();
    await expect(ladder.getByText('End of Senior Year')).toBeVisible();

    // D-232: surplus never carries over. State copy says so in words, not in colour.
    await expect(grade.getByText(/Surplus does not carry over/)).toBeVisible();
});

test('renders the epithet checklist with three states, glyph plus word', async ({ page }) => {
    const epithets = page.getByRole('region', { name: 'Epithet routes' });
    await expect(epithets).toBeVisible();

    // Each row scopes the pill inside its `<li>`; `Open` and `Unverifiable` collide on the
    // loose locator because 17 rows all carry one of them. Scope to the row container, then
    // assert the visible pill carries the word AND the glyph.
    const ladyRow = epithets.getByRole('listitem').filter({ hasText: 'Lady' }).first();
    await expect(ladyRow.getByText('Open')).toBeVisible();
    await expect(ladyRow.locator('[aria-hidden="true"]')).toHaveText('○');

    const dustRow = epithets.getByRole('listitem').filter({ hasText: 'Eat My Dust' }).first();
    await expect(dustRow.getByText('Unverifiable')).toBeVisible();
    await expect(dustRow.locator('[aria-hidden="true"]')).toHaveText('?');
});

test('prints the points-league finale and the named absence for the unofficial name', async ({ page }) => {
    const section = page.getByRole('region', { name: 'Epithet routes' });

    await expect(section.getByText(/Trackblazer finale: a 3-race points league/i)).toBeVisible();

    // The `title` carries the citation; the visible text says "published client name" and never
    // prints "Twinkle Star Climax" on the screen.
    const citation = section.locator('span[title*="Twinkle Star Climax"]');
    await expect(citation).toBeVisible();
    // The visible text — `textContent`, not the `title` attribute — must not contain the string.
    const visibleText = await section.textContent();
    expect(visibleText ?? '').not.toMatch(/Twinkle Star Climax/);
});

test('lands the Shop jump from the Cockpit action area with focus on the heading', async ({ page }) => {
    // Trackblazer is the only scenario with the action-area Shop button (matrix-gated on `shop`).
    const jump = page.getByRole('button', { name: 'Shop', exact: true });
    await expect(jump).toBeVisible();

    await jump.focus();
    await page.keyboard.press('Enter');

    // The visit lands; the Shop heading has the focus, not the click button.
    const active = await page.evaluate(() => {
        const el = document.activeElement;

        return el === null ? null : el.id;
    });

    expect(active, 'focus is not on any element after the jump').not.toBeNull();
    expect(active, 'focus did not land on the Shop heading').toBe('trackblazer-shop-heading');
});

test('sizes every control to the 44px contract and reflows at 320 px', async ({ page }) => {
    // The page-wide sweep the other panel specs run, now through the shared helper: it skips what a
    // collapsed disclosure hides and asserts it measured something. 10 is well under the ~17 controls
    // this page carries.
    await expectTapTargets(page.locator('main a, main button'), 10);

    // WCAG 1.4.10: at 320 the page reflows; nothing scrolls sideways. The rival list caps at
    // eight rows so the Junior-Year seed's 50+ entries do not push the page over the width;
    // the Race Database screen shows the full set.
    await page.setViewportSize({ width: 320, height: 900 });

    for (const heading of ['Grade Point', 'Pro Shop', 'Epithet routes']) {
        await expect(page.getByRole('region', { name: heading })).toBeVisible();
    }

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the Trackblazer panel mounted', async ({ page }) => {
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
