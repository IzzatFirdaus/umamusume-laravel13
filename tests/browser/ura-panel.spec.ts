import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * SCREEN-014, the URA panel (plan §9 E2, `SCR-CAR-020`).
 *
 * `UraPanelTest` pins the payload; these cases assert what only a browser reaches: the state words
 * beside their glyphs, the `N/A` readings and the sentences behind them, the Critical alert as
 * rendered copy, and the fact that a cockpit whose matrix leaves the flag off draws none of it.
 *
 * **Runs are created through the wizard, never by id.** The due-race state needs eleven logged turns,
 * so the turns go through `runs.turns.store` — the route and Form Request that already own a turn
 * write — rather than eleven rounds of the raw form's six fields. The UI path for that write is
 * `career-cockpit.spec.ts`'s job; this spec's subject is what the panel renders once the state exists.
 *
 * It assumes a **seeded catalogue**: the debut row (`Junior Make Debut`, turn 12, mandatory) comes
 * from `race_catalog_slots`, so this runs against the dev file or a scratch copy of it, exactly as
 * the other career specs assume a seeded `Rice Shower`.
 *
 * Glyphs are `aria-hidden`, so every state assertion reads the **word**, never the mark.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** The turn the calendar puts the debut at, and the run must therefore pass to make it due. */
const DEBUT_TURN = 12;

async function recordTurns(page: import('@playwright/test').Page, runId: string, count: number): Promise<void> {
    for (let turn = 1; turn <= count; turn++) {
        // Laravel refreshes the XSRF cookie on every response, so the header is read again for each
        // write rather than once for the loop: one stale token and the next POST is a 419.
        const xsrf = await page
            .context()
            .cookies()
            .then((all) => all.find((c) => c.name === 'XSRF-TOKEN')?.value);

        expect(xsrf, `the session carries no XSRF-TOKEN cookie before turn ${turn}`).toBeDefined();

        const response = await page.request.post(`/training-runs/${runId}/turns`, {
            form: {
                turn: String(turn),
                speed: '600',
                stamina: '600',
                power: '600',
                guts: '600',
                wit: '600',
            },
            headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
        });

        expect(response.status(), `turn ${turn} was refused`).toBeLessThan(400);
    }
}

/** A URA career with `turns` logged turns recorded, opened on its Cockpit. */
async function openUraCockpit(
    page: import('@playwright/test').Page,
    turns: number,
): Promise<import('@playwright/test').Locator> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'ura_finale' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    // The bare record URL, not the Cockpit the create redirect lands on: the turns are written against
    // the id it carries, and the Cockpit is reached by appending to it.
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, turns);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    // The region is the content-level anchor: waiting on `#app > *` alone can resolve while Inertia is
    // still swapping the page, and a control that has not been placed yet measures zero.
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();

    return page.getByRole('region', { name: 'Scenario' });
}

test('draws the three modules with the recorded mandatory races as text', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    await expect(region.getByRole('heading', { name: 'Career goals' })).toBeVisible();
    await expect(region.getByRole('heading', { name: 'Mandatory races' })).toBeVisible();
    await expect(region.getByRole('heading', { name: 'Happy Meek' })).toBeVisible();

    // The calendar's own rows, each with the word rather than the glyph.
    await expect(region.getByText('Junior Make Debut')).toBeVisible();
    await expect(region.getByText(/^URA Finals Final/)).toBeVisible();
    await expect(region.getByText('Upcoming').first()).toBeVisible();
});

test('states the career goals absence, what it costs and what unblocks it', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    const statement = region.getByText(/trainee_goals/);
    await expect(statement).toBeVisible();
    await expect(statement).toContainText('PRD');

    // The reading itself is N/A with the same sentence as its title, never a blank or a dash.
    await expect(region.getByTitle(/trainee_goals/).first()).toHaveText('N/A');
});

test('renders each Happy Meek field as an unrecorded reading with its own reason', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    await expect(region.getByText('Current level')).toBeVisible();
    await expect(region.getByText('Duel availability')).toBeVisible();
    await expect(region.getByText('Potential reward')).toBeVisible();
    await expect(region.getByText('Final-race contribution')).toBeVisible();

    // Nothing is stored, so every one of the four prints N/A rather than a figure the tool measured.
    const naCells = region.locator('span[title]:has-text("N/A")');
    expect(await naCells.count()).toBeGreaterThanOrEqual(4);
});

test('raises the Critical alert as rendered copy when the debut turn has arrived', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    await expect(region.getByText('Critical: Junior Make Debut is due and not recorded')).toBeVisible();
    await expect(region.getByText('Due now')).toBeVisible();

    // The alert is a note, not only a colour: the level word is in the rendered text.
    await expect(region.getByRole('link', { name: 'Record this race' }).first()).toBeVisible();
});

test('marks the debut missed once its turn has passed with no race recorded', async ({ page }) => {
    // Twelve logged turns and no race entry: turn 12 is behind the run, so this is the `missed`
    // state. `completed` needs a race written through the race screen, which is D10's own surface;
    // the props test covers that arm, and this one covers the three states the turn writes reach.
    const region = await openUraCockpit(page, DEBUT_TURN);

    await expect(region.getByText('Missed')).toBeVisible();
    await expect(region.getByText('Critical: Junior Make Debut is due and not recorded')).toBeVisible();
});

test('gives the finale block an N/A deadline instead of an invented turn', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    // The URA calendar holds three mandatory rounds with no turn recorded: the Qualifier, the
    // Semifinal and this scenario's Final. Each gets `N/A` rather than a guessed turn, so the count
    // of no-turn cells equals the count of finale rows, and only the debut row prints a number.
    const finals = region.getByText(/^URA Finals/);
    await expect(finals).toHaveCount(3);

    const cells = region.getByTitle(/no turn/i);
    await expect(cells).toHaveCount(await finals.count());
    await expect(cells.first()).toHaveText('N/A');
});

test('draws no URA module in a cockpit whose matrix leaves the flag off', async ({ page }) => {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const region = page.getByRole('region', { name: 'Scenario' });
    await expect(region.getByRole('heading', { name: 'Happy Meek' })).toHaveCount(0);
    await expect(region.getByRole('heading', { name: 'Mandatory races' })).toHaveCount(0);
    await expect(region.getByText(/trainee_goals/)).toHaveCount(0);
});

test('reaches the race door from the keyboard without losing focus', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    // 'Record this race' is app copy, no client source: the label is asserted as the tool's own words.
    const door = region.getByRole('link', { name: 'Record this race' }).first();
    await expect(door).toBeVisible();

    await door.focus();
    await expect(door).toBeFocused();
    const outline = await door.evaluate((el: HTMLElement) => getComputedStyle(el).outlineStyle);
    expect(outline, 'the door has no visible focus outline').not.toBe('none');

    await door.press('Enter');
    await page.waitForURL(/\/races/, { waitUntil: 'domcontentloaded' });
});

test('keeps the 44px floor and reflows at 320 px with the panel drawn', async ({ page }) => {
    await openUraCockpit(page, DEBUT_TURN - 1);

    // The page-wide sweep through the shared helper, which is where the collapsed-disclosure rule now
    // lives (KI-92). 9 is the `> 8` this spec already held: enough to prove the sweep measured a page,
    // and it is what keeps the helper's skip from becoming a way to pass on nothing.
    await expectTapTargets(page.locator('main a, main button'), 9);

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the URA panel mounted', async ({ page }) => {
    await openUraCockpit(page, DEBUT_TURN - 1);

    await buildAxe(page).analyze();
});
