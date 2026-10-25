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
 *
 * Dated note 2026-10-25 (owner ruling A): the URA-only panel registrations were retired. This file
 * keeps the surfaces that render through `career_goals` — the Trainee goals, Mandatory races and
 * Happy Meek groups — plus the flag-off composition case. The removed cases tested `UraDuelPanel`
 * (deleted by FQ-12), the `career_milestones` flag, the `pre_final_review` flag, and the separate
 * `happy_meek` panel; no scenario's matrix can compose any of those.
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

async function postTurn(
    page: import('@playwright/test').Page,
    runId: string,
    fields: Record<string, string>,
): Promise<void> {
    // Laravel refreshes the XSRF cookie on every response, so the header is read again for each
    // write rather than once for the loop: one stale token and the next POST is a 419.
    const xsrf = await page
        .context()
        .cookies()
        .then((all) => all.find((c) => c.name === 'XSRF-TOKEN')?.value);

    expect(xsrf, `the session carries no XSRF-TOKEN cookie before turn ${fields.turn}`).toBeDefined();

    const response = await page.request.post(`/training-runs/${runId}/turns`, {
        form: { speed: '600', stamina: '600', power: '600', guts: '600', wit: '600', ...fields },
        headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
    });

    expect(response.status(), `turn ${fields.turn} was refused`).toBeLessThan(400);
}

/** The turns a state needs, recorded in order through the route that owns the write. */
async function recordTurns(page: import('@playwright/test').Page, runId: string, count: number): Promise<void> {
    for (let turn = 1; turn <= count; turn++) {
        await postTurn(page, runId, { turn: String(turn) });
    }
}

/** A URA career made through the wizard, returned as its bare record URL. */
async function createUraRun(page: import('@playwright/test').Page): Promise<string> {
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

    return runUrl;
}

/** The Scenario region of a run's Cockpit, once Inertia has placed it. */
async function openScenarioRegion(
    page: import('@playwright/test').Page,
    runUrl: string,
): Promise<import('@playwright/test').Locator> {
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The region is the content-level anchor: waiting on `#app > *` alone can resolve while Inertia is
    // still swapping the page, and a control that has not been placed yet measures zero.
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();

    return page.getByRole('region', { name: 'Scenario' });
}

const runIdOf = (runUrl: string): string => runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

/** A URA career with `turns` logged turns recorded, opened on its Cockpit. */
async function openUraCockpit(
    page: import('@playwright/test').Page,
    turns: number,
): Promise<import('@playwright/test').Locator> {
    const runUrl = await createUraRun(page);
    await recordTurns(page, runIdOf(runUrl), turns);

    return openScenarioRegion(page, runUrl);
}

test('draws the three modules with the recorded mandatory races as text', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    await expect(region.getByRole('heading', { name: 'Trainee goals' })).toBeVisible();
    await expect(region.getByRole('heading', { name: 'Mandatory races' })).toBeVisible();
    await expect(region.getByRole('heading', { name: 'Happy Meek' })).toBeVisible();

    // The calendar's own rows, each with the word rather than the glyph. Scoped to the group they
    // belong to since the ladder prints the debut's name too, as one of her own objectives: the two
    // lists are different facts about the same race, and a region-wide word match cannot tell them.
    const races = region.getByRole('region', { name: 'Mandatory races' });
    await expect(races.getByText('Junior Make Debut')).toBeVisible();
    await expect(races.getByText(/^URA Finals Final/)).toBeVisible();
    await expect(races.getByText('Upcoming').first()).toBeVisible();
});

// ADR-0026 replaced this case's subject. What it used to prove, that the panel named the missing
// per-character objective table and said what would unblock it, is retired: the table is fetched, and
// `TraineeGoalsPanelTest` pins the payload. What only a browser shows is the ladder grammar the client
// itself prints, the one due-now rung and the footer count, rendered rather than composed.
test('prints her own ladder with one rung due and the client footer', async ({ page }) => {
    // Eleven logged turns makes turn 12 the next one to play, which is where her debut sits.
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    // Scoped to the group that owns the marker. A region-wide word match reads the Mandatory races
    // group's identical word too, and the two lists are different facts about the same race; this is
    // the collision class that bit the E3 and E4 panel specs.
    const goals = region.getByRole('region', { name: 'Trainee goals' });

    const rows = goals.getByRole('listitem');
    // Rice Shower's eleven stored objectives, three of them the post-December rounds the Mandatory
    // races group renders, leave eight rungs.
    await expect(rows).toHaveCount(8);

    await expect(rows.first()).toContainText('Participate in Junior Make Debut');
    await expect(rows.last()).toContainText('Place 1st in Arima Kinen');

    // One rung due, and only one, whatever the count (plan §5.3), and it is the earliest unmet goal.
    await expect(goals.getByText('Due now')).toHaveCount(1);
    await expect(rows.first()).toContainText('Due now');

    // The client's own footer shape, both integers: no bar, no percentage, no score (fidelity §2.4,
    // ux-laws §9 item 6, PRD §6.11).
    await expect(goals.getByText('Goals Achieved 0 of 8')).toBeVisible();
});

test('renders each Happy Meek field as an unrecorded reading with its own reason', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    await expect(region.getByText('Current level')).toBeVisible();
    await expect(region.getByText('Duel availability')).toBeVisible();
    await expect(region.getByText('Potential reward')).toBeVisible();
    await expect(region.getByText('Final-race contribution')).toBeVisible();

    // Nothing is stored, so every one of the four prints N/A with its reason as visible prose. The
    // rows render inside the Happy Meek group `career_goals` composes; the separate `happy_meek`
    // panel flag was retired (owner ruling A, 2026-10-25).
    const happyMeek = region.getByRole('region', { name: 'Happy Meek' });
    await expect(happyMeek.getByText('N/A', { exact: true })).toHaveCount(4);
    await expect(happyMeek.getByText(/No column stores a Happy Meek level/)).toBeVisible();
});

test('raises the Critical alert as rendered copy when the debut turn has arrived', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    await expect(region.getByText('Critical: Junior Make Debut is due and not recorded')).toBeVisible();
    await expect(region.getByRole('region', { name: 'Mandatory races' }).getByText('Due now')).toBeVisible();

    // The alert is a note, not only a colour: the level word is in the rendered text.
    await expect(region.getByRole('link', { name: 'Record this race' }).first()).toBeVisible();
});

test('marks the debut missed once its turn has passed with no race recorded', async ({ page }) => {
    // Twelve logged turns and no race entry: turn 12 is behind the run, so this is the `missed`
    // state. `completed` needs a race written through the race screen, which is D10's own surface;
    // the props test covers that arm, and this one covers the three states the turn writes reach.
    const region = await openUraCockpit(page, DEBUT_TURN);

    // Both groups read the debut as missed, and each says it in its own list: the calendar's
    // obligation and one of her own rungs are two facts about one race, so the assertion is scoped
    // twice rather than counted once across the region.
    await expect(region.getByRole('region', { name: 'Trainee goals' }).getByText('Missed')).toBeVisible();
    await expect(region.getByRole('region', { name: 'Mandatory races' }).getByText('Missed')).toBeVisible();
    await expect(region.getByText('Critical: Junior Make Debut is due and not recorded')).toBeVisible();
});

test('gives the finale block an N/A deadline instead of an invented turn', async ({ page }) => {
    const region = await openUraCockpit(page, 0);

    // The URA calendar holds three mandatory rounds with no turn recorded: the Qualifier, the
    // Semifinal and this scenario's Final. Each gets `N/A` rather than a guessed turn, so the count
    // of no-turn cells equals the count of finale rows, and only the debut row prints a number.
    //
    // Scoped to the `Mandatory races` region since G3 added a second list to the same panel: the
    // phrase the count keyed on is plain English, and the milestone rows use it too. The assertion is
    // the same equality, read from the list it describes rather than from the whole Scenario region.
    const races = region.getByRole('region', { name: 'Mandatory races' });
    const finals = races.getByText(/^URA Finals/);
    await expect(finals).toHaveCount(3);

    const cells = races.getByTitle(/no turn/i);
    await expect(cells).toHaveCount(await finals.count());
    await expect(cells.first()).toHaveText('N/A');
});

test('draws no URA module in a cockpit whose matrix leaves the flag off', async ({ page }) => {
    // Trackblazer, not Unity Cup: `career_goals` is on for Unity Cup and the Grand Concert by owner
    // ruling (ADR-0026), so this case's own premise, a matrix that says off, only holds for the
    // scenario whose objectives are Grade Point deadlines. `UraPanelTest:80` pins the same three
    // readings of the switch on the payload side.
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const region = page.getByRole('region', { name: 'Scenario' });
    await expect(region.getByRole('heading', { name: 'Happy Meek' })).toHaveCount(0);
    await expect(region.getByRole('heading', { name: 'Mandatory races' })).toHaveCount(0);
    // The ladder is a group of this module, so a matrix that says off draws none of it: the absent
    // region is the live contract, where the retired case asserted an absence sentence instead.
    await expect(region.getByRole('region', { name: 'Trainee goals' })).toHaveCount(0);
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
