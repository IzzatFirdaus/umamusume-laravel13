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

    // Nothing is stored, so every one of the four prints N/A rather than a figure the tool measured.
    const naCells = region.locator('span[title]:has-text("N/A")');
    expect(await naCells.count()).toBeGreaterThanOrEqual(4);
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

/*
 * G3: the three decision surfaces. `UraFinaleSurfacesTest` pins the payload; these cases assert what
 * only a browser reaches — the state word beside its mark in an ordered list, the duel panel's
 * keyboard path, and the pre-final section's appearance and disappearance on the turn the run is on.
 *
 * The turns are written as a single POST each rather than as a sequence: a gate's `missed` state needs
 * the career *past* the 72-turn grid, and seventy-two round trips would be the slowest test in the
 * file for one row of copy. The store route validates a unique turn number, not a run of them.
 */

/** The milestone list, found through the section's own accessible name rather than through its text. */
function milestoneList(region: import('@playwright/test').Locator): import('@playwright/test').Locator {
    return region.getByRole('region', { name: 'Career milestones' }).getByRole('list');
}

test('draws the three fan gates as an ordered list, each with its state word and both figures', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    const list = milestoneList(region);
    await expect(region.getByRole('heading', { name: 'Career milestones' })).toBeVisible();
    await expect(list.getByRole('listitem')).toHaveCount(3);

    // The figures the matrix publishes, both of them, because no column says which one binds.
    await expect(list.getByText('60,000 Turf / 40,000 Dirt')).toBeVisible();
    await expect(list.getByText('120,000 Turf / 80,000 Dirt')).toBeVisible();

    // Glyphs are aria-hidden, so the state is asserted as the word beside them.
    await expect(list.getByText('Upcoming')).toHaveCount(3);

    // Each row is reachable by keyboard through its own door, and no turn is invented for it.
    await expect(list.getByRole('link', { name: 'Race options for this gate' })).toHaveCount(3);
    await expect(list.getByText('turn N/A').first()).toBeVisible();
});

test('leaves a gate whose state depends on an unrecorded track as N/A with the sentence', async ({ page }) => {
    const runUrl = await createUraRun(page);
    await postTurn(page, runIdOf(runUrl), { turn: '20', fans: '50000' });
    const region = await openScenarioRegion(page, runUrl);

    const valentines = milestoneList(region).getByRole('listitem').filter({ hasText: 'Valentine' });
    await expect(valentines.getByText('N/A').first()).toBeVisible();
    await expect(valentines.getByText(/whether this trainee races on turf or dirt/)).toBeVisible();

    // The two gates far enough above the reading settle on the bounds without naming a track.
    await expect(milestoneList(region).getByText('Upcoming')).toHaveCount(2);
});

test('marks a gate missed once the finale block is behind the run', async ({ page }) => {
    const runUrl = await createUraRun(page);
    // Turn 72 is the last of the 24-turn Senior year, so the block after it has arrived.
    await postTurn(page, runIdOf(runUrl), { turn: '72', fans: '10000' });
    const region = await openScenarioRegion(page, runUrl);

    await expect(milestoneList(region).getByText('Missed')).toHaveCount(3);
});

test('opens and closes the duel panel by keyboard and returns focus to the door', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    const door = region.getByRole('button', { name: 'Happy Meek duel panel' });
    await door.focus();
    await expect(door).toBeFocused();
    await door.press('Enter');

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByText('Past My Limits, 200 or more Skill Points')).toBeVisible();
    await expect(dialog.getByRole('heading', { name: 'Racing Spirit hints' })).toBeVisible();

    // Nothing on this surface prints a percentage, and the empty figures say why.
    const text = await dialog.innerText();
    expect(text).not.toContain('%');
    await expect(dialog.getByText('N/A').first()).toBeVisible();

    // Ruling 2a: every absence in the dialog is `N/A` plus its reason with no provenance badge —
    // `unknown` beside an absence was retired, so no Unknown chip may appear on any of the rows.
    await expect(dialog.getByRole('img', { name: /^Unknown/ })).toHaveCount(0);

    // Escape is the platform's own close for a modal dialog; the return is this component's.
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(door).toBeFocused();
});

test('keeps the duel dialog usable at 320 px and clears the 44 px floor inside it', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 700 });
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    await region.getByRole('button', { name: 'Happy Meek duel panel' }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the duel dialog scrolls the page sideways at 320px').toBeLessThanOrEqual(1);

    // The drawer's own control, measured rather than assumed: it is not in `main`, so the page-wide
    // sweep above never sees it.
    const close = dialog.getByRole('button', { name: 'Close' });
    const box = await close.boundingBox();
    expect(box, 'the dialog renders no Close control').not.toBeNull();
    expect((box as { height: number }).height).toBeGreaterThanOrEqual(44);
});

test('passes the axe A + AA scan with the duel dialog open', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    await region.getByRole('button', { name: 'Happy Meek duel panel' }).click();
    await expect(page.getByRole('dialog')).toBeVisible();

    await buildAxe(page).analyze();
});

test('shows the pre-final review only inside the window the advisor looks ahead', async ({ page }) => {
    const region = await openUraCockpit(page, DEBUT_TURN - 1);

    // Eleven turns in, the finale is nowhere near, and the section is absent rather than empty.
    await expect(region.getByRole('heading', { name: 'Pre-final review' })).toHaveCount(0);

    const runUrl = await createUraRun(page);
    await postTurn(page, runIdOf(runUrl), { turn: '70', fans: '50000', speed: '620' });
    const near = await openScenarioRegion(page, runUrl);

    const section = near.getByRole('heading', { name: 'Pre-final review' });
    await expect(section).toBeVisible();
    await expect(near.getByText(/Senior turn|in 2 turns/)).toBeVisible();
    await expect(near.getByText('around 800 Speed, or an A rank')).toBeVisible();
    // The hedge stays with the figure, and the run's own shortfall reads as arithmetic, not a rule.
    await expect(near.getByText('180 short of the approximation')).toBeVisible();
    await expect(near.getByText('This run\'s finale distance')).toBeVisible();
});
