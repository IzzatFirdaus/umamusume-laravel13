import { openScenarioDetail } from '../utils/scenario-detail';
import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { expectTapTargets } from '../utils/tap-targets';
import { startCareer } from '../utils/start-career';

/*
 * The Cockpit's write controls (F2, plan §9.6 rulings 1, 2 and 4): the three header edit forms (status,
 * scenario, grade-point period), the arbitrary-turn correction selector with its save and delete, the
 * run-delete disclosure, and the two export links. `CareerCockpitTest` asserts the payloads these
 * forms post; this file is where the 44px contract, the keyboard path, the two-step disclosure pattern
 * and the redirect targets are proven in a browser.
 *
 * Named `career-cockpit-forms` to distinguish it from `career-cockpit.spec.ts`, which covers the read
 * surfaces (layout, advisor, action grid, axe).
 *
 * Fixture strategy. The run is built by walking the wizard's own steps and pressing `Start Career`, as
 * `career-cockpit.spec.ts` does, with the scenario selectable so the grade-point period form
 * (trackblazer only) can be reached. The row is deleted over HTTP in `afterEach`; the run-delete case
 * removes its own URL from the list first, because it has already deleted the row through the UI.
 *
 * `WRITE` is the 60s budget `career-cockpit.spec.ts` records: `php artisan serve` is a single-process
 * `php -S`, so a step's PUT queues behind whatever else is on the box.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

const WRITE = { timeout: 60_000 } as const;

/** One career, built by the wizard's own steps, ending on the Cockpit. Returns the Cockpit URL. */
async function newCareer(page: import('@playwright/test').Page, scenarioId = 'unity_cup'): Promise<string> {
    // The shared walk (slice 4.1). The private copy it replaced still named `#scenario-<id>` with
    // `aria-pressed`, a card labelled "Select Trainee", `#deck-pick` with "Equip owned to", and
    // "Start Career" — all retired by later rebuilds.
    createdRunUrls.push(await startCareer(page, scenarioId));

    return page.url();
}

/** The Cockpit for a freshly created career: `Start Career` already lands on it. */
async function openCockpit(page: import('@playwright/test').Page, scenarioId = 'unity_cup'): Promise<string> {
    const url = await newCareer(page, scenarioId);
    await page.locator('#app > *').first().waitFor();

    // The Unity Cup panels render inside the closed scenario-detail disclosure; the cases below
    // assert on them, so the shared entry point opens it.
    await openScenarioDetail(page);


    return url;
}


/**
 * The run settings door (`C6b.7`). The identity forms moved off the Cockpit, so a case that drives
 * one navigates here instead of opening a disclosure that no longer exists.
 */
async function openSettings(page: import('@playwright/test').Page): Promise<void> {
    const runId = /\/training-runs\/(\d+)/.exec(page.url())?.[1] ?? '';
    await page.goto(`/training-runs/${runId}/settings`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}
/** The career bar that prints the trainee, scenario and status. */
const careerBar = (page: import('@playwright/test').Page) => page.locator('section[aria-label="Career"]');

test('changes the run status through the settings door', async ({ page }) => {
    await openCockpit(page);
    await openSettings(page);

    const status = page.locator('select#status');
    await expect(status).toHaveValue('Active');
    await status.selectOption('Completed');
    await page.getByRole('button', { name: 'Change status' }).click();

    // The write returns to this door (`back(fallback:)`) and the value survives a reload.
    await expect(page.getByText('Saved.')).toBeVisible(WRITE);
    await openSettings(page);
    await expect(page.locator('select#status')).toHaveValue('Completed');
});

test('changes the run scenario through the settings door', async ({ page }) => {
    await openCockpit(page);
    await openSettings(page);

    const scenario = page.locator('select#scenario');
    await expect(scenario).toHaveValue('unity_cup');
    await scenario.selectOption('ura_finale');
    await page.getByRole('button', { name: 'Change scenario' }).click();

    await expect(page.getByText('Saved.')).toBeVisible(WRITE);
    await openSettings(page);
    await expect(page.locator('select#scenario')).toHaveValue('ura_finale');
});

test('reports the grade-point period on a scenario that composes one', async ({ page }) => {
    await openCockpit(page, 'trackblazer');
    await openSettings(page);

    const period = page.locator('select#current_objective_index');
    await expect(period).toBeVisible();
    await expect(period).toHaveValue('');
    await period.selectOption('1');
    await page.getByRole('button', { name: 'Report period' }).click();

    await expect(page.getByText('Saved.')).toBeVisible(WRITE);
    await openSettings(page);
    await expect(page.locator('select#current_objective_index')).toHaveValue('1');
});

test('records the trainee rarity, potential and growth rate on the settings door', async ({ page }) => {
    await openCockpit(page);
    await openSettings(page);

    await page.selectOption('#trainee_rarity', '3');
    await page.locator('#potential_level').fill('2');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="growth_rate[${stat}]"]`).fill(stat === 'Stamina' ? '10' : '0');
    }

    await page.getByRole('button', { name: 'Save identity' }).click();

    await expect(page.getByText('Saved.')).toBeVisible(WRITE);
    await openSettings(page);
    await expect(page.locator('#trainee_rarity')).toHaveValue('3');
    await expect(page.locator('#potential_level')).toHaveValue('2');
});

test('records the Unity Cup team state through the team editor', async ({ page }) => {
    await openCockpit(page);

    // The editor is a disclosure, so its controls have no box until it opens: the sweep runs after the
    // click, which is the KI-92 lesson. On an Active career the wrapper is open by default
    // (`:open="isActive"`, the merge's own design), so the click happens only when the panel is
    // closed — an unconditional toggle would close the panel this case is trying to open.
    const editor = page.locator('details', { has: page.getByText('Edit team state') });
    if (!(await editor.evaluate((el) => el instanceof HTMLDetailsElement && el.open))) {
        await editor.locator('summary').click();
    }

    await expectTapTargets(editor.locator('input, button'), 8);

    await editor.locator('input[name="team_name"]').fill('Blue Bloom');
    await editor.locator('input[name="team_motto"]').fill('Dreaming Big');
    await editor.locator('input[name="team_league_placement"]').fill('8');
    await editor.locator('input[name="team_preseason_wins"]').fill('4');
    await editor.locator('input[name="unity_trainings_count"]').fill('55');
    await editor.locator('input[name="spirit_bursts_count"]').fill('6');
    await editor.locator('input[name="extreme_bursts_count"]').fill('5');

    await editor.getByRole('button', { name: 'Save team state' }).click();

    // The write redirects back to the Cockpit, where the Team panel prints the team it was told about.
    // The editor's own inputs hold the same words, but `getByText` reads text nodes, not field values.
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    // The Team row is the read-back. It is matched as one cell because the editor now renders at page
    // level and its own read-out would match a bare team name twice (strict mode).
    await expect(page.getByRole('cell', { name: /Blue Bloom/ })).toBeVisible();

    // KI-97: the same write has to reach the header strip, whose own caption reads "normal plus
    // Extreme". Before the fix the rail printed N/A over a recorded 6 and 5, so the assertion is on
    // the box that owns both the label and the value rather than on a bare figure.
    const burstsBox = page.locator('div.min-w-36').filter({ hasText: 'Spirit Bursts' });
    await expect(burstsBox).toHaveCount(1);
    await expect(burstsBox).toContainText('11');
});

test('records the per-stat team grades and reads them back on the panel', async ({ page }) => {
    await openCockpit(page);

    const grades = page.getByRole('list', { name: 'Team stat grades' });
    const rows = grades.getByRole('listitem');
    await expect(rows).toHaveCount(5);
    await expect(rows.first()).toContainText('N/A');

    const editor = page.locator('details', { has: page.getByText('Edit team state') });
    if (!(await editor.evaluate((el) => el instanceof HTMLDetailsElement && el.open))) {
        await editor.locator('summary').click();
    }

    // R10: the per-stat set stops at S; S+ is the overall team rank and has no option here.
    await expect(editor.locator('#team_stat_grade_Speed option', { hasText: 'S+' })).toHaveCount(0);

    for (const [stat, grade] of [['Speed', 'S'], ['Stamina', 'A'], ['Power', 'A'], ['Guts', 'A'], ['Wit', 'A']] as const) {
        await editor.locator(`#team_stat_grade_${stat}`).selectOption(grade);
    }

    await editor.getByRole('button', { name: 'Save team state' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    for (const [stat, grade] of [['Speed', 'S'], ['Stamina', 'A'], ['Power', 'A'], ['Guts', 'A'], ['Wit', 'A']] as const) {
        await expect(rows.filter({ hasText: stat }).locator('span').nth(1)).toHaveText(grade);
    }
});

test('renders the Unity Cup scenario region without a runtime error, the bursts widget as a meter', async ({ page }) => {
    // Regression for the cockpit-wt merge's SpiritBurstsPanel registration, removed 2026-10-16:
    // `spirit_bursts` is a widget key, and the panel-shaped component registered against it
    // received the widget shape and crashed every Unity Cup Cockpit render on
    // `props.scenario.alerts`. The assertions are on the rendered page, not on the source, so
    // re-introducing that registration — or registering any panel-shaped component against a
    // widget key — reproduces the crash and fails here rather than passing silently.
    const problems: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            problems.push(message.text());
        }
    });
    page.on('pageerror', (error) => problems.push(error.message));

    await openCockpit(page);

    // The scenario strip renders, including the scenario's own ruleset date line.
    await expect(page.locator('[data-region="scenario"]').getByText('Unity Cup')).toBeVisible();
    await expect(page.locator('[data-region="scenario-ruleset-date"]')).toBeVisible();

    // The bursts widget renders as the labelled meter, its own shape: the value is the unrecorded
    // reading the matrix's widget carries, and the bar names it.
    await expect(page.getByRole('img', { name: 'Spirit Bursts: not recorded' })).toBeVisible();

    // And the crash the bad registration produced never reaches the console.
    expect(
        problems.filter((problem) => /TypeError|Cannot read properties of undefined/.test(problem)),
        problems.join('\n'),
    ).toEqual([]);
});

test('closing the team editor without saving records nothing', async ({ page }) => {
    await openCockpit(page);

    const editor = page.locator('details', { has: page.getByText('Edit team state') });
    // Open only when closed: on an Active career the wrapper is open by default, and an
    // unconditional first click would close it before the fill.
    if (!(await editor.evaluate((el) => el instanceof HTMLDetailsElement && el.open))) {
        await editor.locator('summary').click();
    }
    await editor.locator('input[name="team_name"]').fill('Blue Bloom');

    // Cancel is the disclosure itself: the form submits only from its own button, so closing it
    // abandons the edit. The reload reads the stored run, and a fresh career has no team identity
    // to print — the same absent card `CockpitTeamPanelTest` pins on the wire.
    await editor.locator('summary').click();
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText('Blue Bloom', { exact: true })).toHaveCount(0);
});

test('links the run-scoped deck builder from the Cockpit', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // `C6b.7` deleted the Run-identity section that carried the old link. The surviving door is the
    // Where-to-go card's own Run deck entry, a plain GET anchor to the run's deck.
    const door = page.getByRole('link', { name: 'Run deck' });
    await expect(door).toBeVisible();
    await expect(door).toHaveAttribute('href', new RegExp(`/training-runs/${runId}/deck$`));
});

test('deletes the career through the confirmation door', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runUrl = cockpitUrl.replace(/\/cockpit$/, '');

    // `C6b.3`: the destructive write has its own door, footed on the Cockpit.
    await page.getByRole('link', { name: 'Delete this run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/delete$/, WRITE);

    // Refused until the confirmation is ticked.
    const submit = page.getByRole('button', { name: 'Delete this career' });
    await expect(submit).toBeDisabled();
    await page.locator('#confirmed').check();
    await submit.click();

    await page.waitForURL(/\/training-runs$/, { waitUntil: 'domcontentloaded' });

    // The row is already gone, so the afterEach must not try to delete it again.
    const index = createdRunUrls.indexOf(runUrl);
    if (index >= 0) {
        createdRunUrls.splice(index, 1);
    }
});

test('sizes the delete-confirmation action to the 44px contract', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);

    // `C6b.3` moved the destructive action out of a collapsed disclosure onto its own page, so the
    // control is laid out on arrival: no disclosure has to be opened first (KI-92).
    await page.goto(`${cockpitUrl.replace(/\/cockpit$/, '')}/delete`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expectTapTargets(page.getByRole('button', { name: 'Delete this career' }));
});

test('sizes the header controls to the 44px contract', async ({ page }) => {
    await openCockpit(page);
    await openSettings(page);

    // WCAG 2.2 SC 2.5.8, floor 44px. The same sweep `career-cockpit.spec.ts` runs, on the door the
    // header forms moved to (`C6b.7`).
    const targets = [
        page.locator('select#status'),
        page.getByRole('button', { name: 'Change status' }),
        page.locator('select#scenario'),
        page.getByRole('button', { name: 'Change scenario' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a run settings control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('walks the header form from the keyboard', async ({ page }) => {
    await openCockpit(page);
    await openSettings(page);

    // The status select is a native control reached by Tab, and the submit is the next stop.
    const status = page.locator('select#status');
    await status.focus();
    await expect(status).toBeFocused();

    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Change status' })).toBeFocused();

    // The submit carries the global focus-visible ring rather than relying on hover (WCAG 2.4.7).
    const outline = await page
        .getByRole('button', { name: 'Change status' })
        .evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the focused submit has no focus ring').toBeGreaterThanOrEqual(2);
});


test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openCockpit(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic: Inertia's NProgress bar is
    // appended to `<body>` as a sibling of the app root and carries an invalid `role="bar"` (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
