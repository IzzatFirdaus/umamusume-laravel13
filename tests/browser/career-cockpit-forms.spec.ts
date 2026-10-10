import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { expectTapTargets } from '../utils/tap-targets';

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
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator(`#scenario-${scenarioId}`).click();
    await expect(page.locator(`#scenario-${scenarioId}`)).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.getByRole('button', { name: 'Select Trainee' }).first();
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.selectOption('select[name="purpose"]', 'StoryClear');
    await page.selectOption('select[name="distance"]', 'Medium');
    await page.selectOption('select[name="surface"]', 'Turf');
    await page.selectOption('select[name="style"]', 'Pace Chaser');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="targets[${stat}]"]`).fill('800');
    }

    await page.getByRole('button', { name: 'Save target' }).click();
    await page.locator('#app > *').first().waitFor();

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#deck-pick').selectOption({ index: 1 });
    await page.getByRole('button', { name: /^Equip owned to/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped', WRITE);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Start Career' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));

    return page.url();
}

/** The Cockpit for a freshly created career: `Start Career` already lands on it. */
async function openCockpit(page: import('@playwright/test').Page, scenarioId = 'unity_cup'): Promise<string> {
    const url = await newCareer(page, scenarioId);
    await page.locator('#app > *').first().waitFor();

    return url;
}

/** The career bar that prints the trainee, scenario and status. */
const careerBar = (page: import('@playwright/test').Page) => page.locator('section[aria-label="Career"]');

test('changes the run status through the header form and prints the new label', async ({ page }) => {
    await openCockpit(page);

    await expect(careerBar(page).getByText('Active')).toBeVisible();

    const status = page.locator('select#status');
    await expect(status).toHaveValue('Active');
    await status.selectOption('Completed');
    await page.getByRole('button', { name: 'Change status' }).click();

    // The write flashes the controller's own line and the career bar prints the new status.
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    await expect(careerBar(page).getByText('Completed')).toBeVisible();
});

test('changes the run scenario through the header form and prints the new label', async ({ page }) => {
    await openCockpit(page);

    const scenario = page.locator('select#scenario');
    await expect(scenario).toHaveValue('unity_cup');
    await scenario.selectOption('ura_finale');
    await page.getByRole('button', { name: 'Change scenario' }).click();

    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    await expect(careerBar(page).getByText('URA Finale')).toBeVisible();
});

test('reports the grade-point period on a scenario that composes one', async ({ page }) => {
    // The period form renders only when the scenario defines grade objectives (trackblazer).
    await openCockpit(page, 'trackblazer');

    const period = page.locator('select#current_objective_index');
    await expect(period).toBeVisible();

    // "Not reported" is the honest first state; the first objective is a real period the Trainer can report.
    await expect(period).toHaveValue('');
    await period.selectOption('1');
    await page.getByRole('button', { name: 'Report period' }).click();

    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    await expect(period).toHaveValue('1');
});

test('switches the correction to another turn through the selector', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // Two turns, so the selector has something to switch between. No rendered surface creates a turn
    // any more, so the fixture writes them over the route (`record-turns.ts`).
    await recordTurns(page, runId, 2, { stamina: '700', power: '700', guts: '700', wit: '700' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const selector = page.locator('#correction-turn-select');
    await expect(selector.locator('option')).toHaveCount(2);

    // The options read "Turn 1" and "Turn 2" and their values are each turn's id. Switching to turn 1
    // navigates `?edit_turn=<id>` with the disclosure kept open, and the form re-syncs to that turn.
    await selector.selectOption({ label: 'Turn 1' });
    await expect(page).toHaveURL(/edit_turn=/);

    const correction = page.locator('details', { has: page.getByText(/Correct turn \d+ by hand/) });
    // `.first()`: the delete disclosure nests inside the correction one, so both carry a `summary`.
    await correction.locator('summary').first().click();
    await expect(correction.locator('input[name="turn"]')).toHaveValue('1');
});

test('saves a correction and returns to the cockpit with the new value', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 1, { stamina: '700', power: '700', guts: '700', wit: '700' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const correction = page.locator('details', { has: page.getByText('Correct turn 1 by hand') });
    // `.first()`: the delete disclosure nests inside the correction one, so both carry a `summary`.
    await correction.locator('summary').first().click();
    await correction.locator('input[name="energy"]').fill('60');
    await correction.getByRole('button', { name: 'Save correction' }).click();

    // The correction is a PUT over the existing turn and returns to the Cockpit: the state panel is
    // the proof the write landed, read beside the stats the advisor ranks against.
    const state = page.locator('section[aria-labelledby="career-state-heading"]');
    await expect(state.getByText('60/100')).toBeVisible(WRITE);
});

test('deletes a turn through the two-step disclosure', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 2);
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const selector = page.locator('#correction-turn-select');
    await expect(selector.locator('option')).toHaveCount(2);

    // The delete door is a second disclosure nested inside the correction panel, which is closed by
    // default: the correction opens first, then the delete door, and the DELETE runs only after the
    // Trainer confirms. The delete summary is addressed by its own text because the outer correction
    // disclosure contains it and would otherwise match too.
    const correction = page.locator('details', { has: page.getByText(/Correct turn \d+ by hand/) });
    await correction.locator('summary').first().click();
    await page.locator('summary').filter({ hasText: 'Delete turn' }).click();
    await page.getByRole('button', { name: /^Delete turn \d+$/ }).click();

    await expect(selector.locator('option')).toHaveCount(1, WRITE);
});

test('deletes the career through the two-step disclosure', async ({ page }) => {
    const cockpitUrl = await openCockpit(page);
    const runUrl = cockpitUrl.replace(/\/cockpit$/, '');

    const deleteDoor = page.locator('details', { has: page.getByText('Delete this career') });
    await deleteDoor.locator('summary').click();
    await page.getByRole('button', { name: 'Delete this career' }).click();

    await page.waitForURL(/\/training-runs$/, { waitUntil: 'domcontentloaded' });

    // The row is already gone, so the afterEach must not try to delete it again.
    const index = createdRunUrls.indexOf(runUrl);
    if (index >= 0) {
        createdRunUrls.splice(index, 1);
    }
});

test('sizes the controls a two-step disclosure reveals once it is open', async ({ page }) => {
    await openCockpit(page);

    // The page-wide sweeps skip a control inside a collapsed `<details>`: the browser does not lay it
    // out, so it has no box and is not a target anyone can hit (KI-92). That skip is only honest if the
    // control is measured where it does become a target, which is what this case does — it opens the
    // run-delete door and asserts the floor on what the disclosure reveals.
    const deleteDoor = page.locator('details', { has: page.getByText('Delete this career') });
    await deleteDoor.locator('summary').click();

    await expectTapTargets(deleteDoor.locator('a, button'));
});

test('links both exports as file downloads', async ({ page }) => {
    await openCockpit(page);

    await expect(page.getByRole('link', { name: 'Download CSV' })).toHaveAttribute('href', /\/export\/csv$/);
    await expect(page.getByRole('link', { name: 'Download JSON' })).toHaveAttribute('href', /\/export\/json$/);
});

test('sizes the header controls to the 44px contract', async ({ page }) => {
    await openCockpit(page);

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The same sweep `career-cockpit.spec.ts` runs.
    const targets = [
        page.locator('select#status'),
        page.getByRole('button', { name: 'Change status' }),
        page.locator('select#scenario'),
        page.getByRole('button', { name: 'Change scenario' }),
        page.getByRole('link', { name: 'Download CSV' }),
        page.getByRole('link', { name: 'Download JSON' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a cockpit header control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('walks the header form from the keyboard', async ({ page }) => {
    await openCockpit(page);

    // The status select is a native control reached by Tab, and the submit is the next stop.
    const status = page.locator('select#status');
    await status.focus();
    await expect(status).toBeFocused();

    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Change status' })).toBeFocused();

    // The submit carries the global `:focus-visible` ring rather than relying on hover (WCAG 2.4.7).
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
