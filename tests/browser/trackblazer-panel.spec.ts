import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';
import { recordTurns } from '../utils/record-turns';

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

test('the race plan prints three periods, their published figures and the gap the plan has not filled', async ({ page }) => {
    const plan = page.getByRole('region', { name: 'Race plan' });
    await expect(plan).toBeVisible();

    const periods = plan.getByRole('list', { name: 'Checkpoint periods' });
    await expect(periods.getByRole('listitem')).toHaveCount(3);

    // 60 / 300 / 300, the standard track only, each with the KI-15 sentence on its badge. The counts
    // are the assertion: one Junior period at 60 and two periods at 300, which is also the proof that
    // no other track leaked in.
    await expect(periods.getByText('60', { exact: true })).toHaveCount(1);
    await expect(periods.getByText('300', { exact: true })).toHaveCount(2);

    // Nothing is entered against any period on a fresh run, so the plan column is N/A and the floor
    // that depends on it is N/A too: three periods, six absences, each with its reason printed.
    await expect(plan.getByText('Entered races')).toHaveCount(3);
    await expect(plan.getByText('N/A', { exact: true })).toHaveCount(6);
    await expect(plan.getByText(/No race is entered against this period/)).toHaveCount(3);

    // The first checkpoint is ahead of the run, so the list is unfolded.
    await expect(plan.getByText(/periods behind it are folded/)).toHaveCount(0);

    // The pointer, not a second route list: progress renders in the Epithet routes arm.
    await expect(plan.getByText(/Route progress is the Epithet routes surface/)).toBeVisible();

    // The flag the scenario's own rule makes true whatever this run has entered.
    await expect(plan.getByText('Rival races cannot be scheduled')).toBeVisible();
});

test('the plan folds and the year-end gate appears once a period is reported', async ({ page }) => {
    await page.selectOption('#current_objective_index', '3');
    await page.getByRole('button', { name: 'Report period' }).click();

    const plan = page.getByRole('region', { name: 'Race plan' });
    await expect(plan.getByText(/periods behind it are folded/)).toBeVisible();
    await expect(plan.getByRole('list', { name: 'Checkpoint periods' }).getByRole('listitem')).toHaveCount(2);

    // The gate the run is working toward. It is a labelled group of its own because the flag row
    // above names the same event: inside this region the fan figure appears once, and the `dt`/`dd`
    // pairs are asserted element-and-text because Chromium computes no accessible name for the term
    // role. The fan figure is published by two sources that agree, the bond figure by two that
    // disagree, and the run's own bond reading has no column to come from.
    const gate = plan.getByRole('group', { name: 'Year-end gate' });
    await expect(gate.getByText('Assessment')).toBeVisible();
    await expect(gate.getByText('60,000', { exact: true })).toBeVisible();
    await expect(gate.getByText(/19 \/ 31 \/ 51 \(uma.guide\) · 15 \/ 30 \/ 50 \(GameWith\)/)).toBeVisible();
    await expect(gate.getByText(/no column/)).toBeVisible();

    // The reported period is emphasised by mark and word, never by colour alone (WCAG 1.4.1).
    await expect(plan.getByText('the period this run reports')).toBeVisible();
});

test('races ahead prints the window with each row its published win value', async ({ page }) => {
    const runId = (runUrl.match(/\/training-runs\/(\d+)$/) as RegExpMatchArray)[1];

    // Eleven logged turns put the decision on Junior turn 12, the first turn the seeded calendar
    // carries. Fewer writes leave the window with nothing to be ahead of.
    await recordTurns(page, runId, 11);
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });

    const calendar = page.getByRole('region', { name: 'Races ahead' });
    await expect(calendar).toBeVisible();

    const rows = calendar.getByRole('list', { name: 'Races still to come' });
    // The group is labelled by `RaceCatalogSlot::yearLabel()`, which says "Junior"; the strip's
    // "Junior Year" comes from `CareerYear::label()`, a different vocabulary. Match the prefix rather
    // than spell one year into the spec.
    const grouped = calendar.getByRole('list', { name: /^Races ahead in/ });

    // The window is three rows, all inside one career period, and every row carries both figures and
    // one consequence line, so the counts are the assertion. A `.first()` here would pass on a single
    // rendered row.
    await expect(rows.getByRole('list')).toHaveCount(1);
    await expect(grouped.getByRole('listitem')).toHaveCount(3);
    await expect(grouped.getByText(/A win pays/)).toHaveCount(3);
    await expect(grouped.getByText(/Skipping this race forgoes/)).toHaveCount(3);

    // An entered race leaves the window: the strip's rule that one race holds one region.
    await expect(calendar.getByText(/has not entered/)).toBeVisible();
});

test('race fatigue states that no chain is entered and keeps both caveats on the page', async ({ page }) => {
    const fatigue = page.getByRole('region', { name: 'Race fatigue' });
    await expect(fatigue).toBeVisible();

    await expect(fatigue.getByText('Chain so far:')).toBeVisible();
    await expect(fatigue.getByText('N/A', { exact: true })).toBeVisible();

    // The count is entered, never derived from the turns with no race logged.
    await expect(fatigue.getByText(/has not entered a consecutive-race count/)).toBeVisible();

    // Both caveats are visible copy. A caveat behind a hover is a caveat nobody reads.
    await expect(fatigue.getByRole('list', { name: 'What this table does not settle' }).getByRole('listitem'))
        .toHaveCount(2);
    await expect(fatigue.getByText(/whether "3 races" counts the race about to run/)).toBeVisible();
    await expect(fatigue.getByText(/no source says which year/)).toBeVisible();

    // The reference table folds behind a native disclosure, closed while no chain is live.
    const summary = fatigue.locator('summary');
    await expect(summary).toContainText('Every row the publisher prints');

    const table = fatigue.getByRole('table');
    await expect(table).toBeHidden();

    await summary.click();
    await expect(table).toBeVisible();

    // One header row plus the five measures the publisher prints, against the four chain lengths.
    await expect(table.getByRole('row')).toHaveCount(6);
    await expect(table.getByRole('cell').filter({ hasText: '90%+' })).toHaveCount(1);
    await expect(fatigue.getByText('Guaranteed recovery events')).toBeVisible();
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

    for (const heading of [
        'Grade Point', 'Pro Shop', 'Epithet routes',
        // G5's three surfaces ride in the grade arm, so the narrow screen has to hold them too.
        'Race plan', 'Races ahead', 'Race fatigue',
    ]) {
        await expect(page.getByRole('region', { name: heading })).toBeVisible();
    }

    // The odds table is the widest thing the panel draws, and it folds behind a disclosure that is
    // closed with no chain recorded. Open it before measuring overflow, or the 320px check proves
    // nothing about the markup a Trainer can actually reach.
    await page.getByRole('region', { name: 'Race fatigue' }).locator('summary').click();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the Trackblazer panel mounted', async ({ page }) => {
    // axe only reports on rendered content, and the odds table starts folded. Open it so the scan
    // covers the caption, the scoped headers and the row cells rather than the summary alone.
    await page.getByRole('region', { name: 'Race fatigue' }).locator('summary').click();
    await expect(page.getByRole('region', { name: 'Race fatigue' }).getByRole('table')).toBeVisible();

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
