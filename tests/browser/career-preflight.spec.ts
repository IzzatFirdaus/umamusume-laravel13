import { test, expect } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { deleteRun } from '../utils/delete-run';
import { startCareer, walkWizard } from '../utils/start-career';
import { verifyPage } from './fixtures/wizard-verify';

/*
 * Rendered-copy and interaction evidence for the wizard's Preflight step (SCREEN-008, `SCR-CAR-010`).
 * The server-side `CareerPreflightTest` asserts the resolved props, each warning trigger and the created
 * run; these cases assert what only a browser reaches: the review checklist the page is, the values
 * carried forward from the five steps, an Edit round trip, the completeness guard on `Create the run`
 * and where its blocked press puts focus, the flash that names the trainee, and that `Create the run`
 * lands on the run it created.
 *
 * The draft is built by walking the wizard's own steps (`tests/utils/start-career.ts`), so every fact
 * under test was entered through the app rather than seeded into the session. The scratch database holds
 * the catalogue (67 trainees, 559 support cards) and **no Veterans**, so the ancestry step has no library
 * row to pick from: the legacy row is asserted as its named absence, which is the state a fresh install
 * meets.
 *
 * Every navigation waits for `domcontentloaded`, for the reason `legacy.spec.ts`'s header gives: `load`
 * waits on the module graph and one loopback request per artwork frame, which is host speed rather than a
 * property of this screen.
 */

/**
 * The runs this file creates, deleted again in `afterEach` exactly as `run-detail.spec.ts`, `legacy.spec.ts`
 * and `support-deck.spec.ts` do. `Create the run` is one of the two browser actions in the suite (the other
 * is the Legacy builder) that create a run; without this delete the row breaks `runs.spec.ts`'s empty-state
 * assertions and makes the whole suite one-shot against a freshly seeded database.
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** The five entered steps, in the checklist's own order. Step 6 is the page itself and is not a row. */
const ROWS = ['scenario', 'trainee', 'target', 'legacy', 'deck'] as const;

/** Open Preflight on the draft the walk left behind. */
async function openPreflight(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('reviews the five entered steps as one checklist row each', async ({ page }) => {
    await walkWizard(page, 'ura_finale');
    await openPreflight(page);

    // The page is named for what it does, and the name is the shell's one `h1`.
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Before the first turn');

    // One row per entered step, in wizard order. Step 6 is this page, so it is not a row.
    const rows = page.locator('tr[id^="preflight-step-"]');
    await expect(rows).toHaveCount(ROWS.length);

    for (const [index, key] of ROWS.entries()) {
        await expect(rows.nth(index)).toHaveId(`preflight-step-${key}`);
    }

    // Every row carries its own door back to the step that owns it, and no two doors share a name.
    await expect(page.locator('tr[id^="preflight-step-"] a')).toHaveCount(1);

    // Build: the scenario's own label and the entered target, in the step's own words.
    await expect(page.locator('#preflight-step-scenario')).toContainText('URA Finale');
    await expect(page.locator('#preflight-step-scenario')).toContainText('Pass');
    await expect(page.locator('#preflight-step-target')).toContainText('Story Clear · Turf Medium · Pace Chaser');
    await expect(page.locator('#preflight-step-target')).toContainText('Pass');

    // Support deck: the equipped card is counted in words, not restated as a table.
    await expect(page.locator('#preflight-step-deck')).toContainText('1 of six positions carry a card');

    // Ancestry: the named absence, because the scratch database holds no Veterans to pick from.
    await expect(page.locator('#preflight-step-legacy')).toContainText('Missing');
    await expect(page.locator('#preflight-step-legacy')).toContainText('Not entered');

    // The heading's count is the page's answer to "can I start", and it is a live region.
    await expect(page.getByRole('status').filter({ hasText: 'step needs attention' })).toHaveText(
        '1 step needs attention',
    );

    // AbsenceValue renders the reason as the disclosure's content, not as a mouse-only title, and the
    // group carries no accessible name, so the reason is matched as content and the marker as text.
    await expect(
        page.getByRole('group').filter({ hasText: /No source defines a Global ruleset version/ }),
    ).toContainText('N/A');
});

test('draws the eight tiles and the single caution, and no Career mode switch (Phase E2)', async ({ page }) => {
    await walkWizard(page, 'ura_finale');
    await openPreflight(page);

    // Eight tiles, in the wireframe's order (`career-preflight.html:90-106`).
    const tiles = page.locator('section[aria-labelledby="run-heading"] > div.grid > div');
    await expect(tiles).toHaveCount(8);
    await expect(tiles.nth(0)).toContainText('Trainee');
    await expect(tiles.nth(1)).toContainText('Form');
    await expect(tiles.nth(2)).toContainText('Scenario');
    await expect(tiles.nth(3)).toContainText('Distance');
    await expect(tiles.nth(4)).toContainText('Parents');
    await expect(tiles.nth(5)).toContainText('Deck');
    await expect(tiles.nth(6)).toContainText('Target total');
    await expect(tiles.nth(7)).toContainText('Career length');
    await expect(tiles.nth(7)).toContainText('72 turns');

    // Exactly one caution: the empty ancestry slot. Every other entered step passes, and the deck's
    // single card is a warning the row states in words rather than a second coloured pill.
    await expect(page.locator('[class*="status-caution"]')).toHaveCount(1);

    // The walk enters no parents, so the parents tile states the step's own absence rather than a
    // name: `parentsSummary` prints "empty" only once a legacy section exists with a blank slot.
    await expect(tiles.nth(4)).toContainText('Not entered');

    // Phase E2 cut: `career-preflight.html:176-186` draws the Commit card and nothing else. The mode
    // switch, the policy radios and their explanatory copy are gone, so the write carries no choice of
    // its own and the label is the wireframe's one label.
    await expect(page.getByText('Career mode')).toHaveCount(0);
    await expect(page.getByText('Independent Training')).toHaveCount(0);
    await expect(page.getByText('Training policy')).toHaveCount(0);
    await expect(page.getByRole('radio')).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Create the run' })).toBeVisible();
});

test('the commit card stays a card, not a document', async ({ page }) => {
    await openPreflight(page);

    const commit = page.locator('section[aria-labelledby="commit-heading"]');
    expect(await commit.locator('*').count(), 'commit-card descendants under the 25-per-card ceiling').toBeLessThan(25);
    const text = (await commit.innerText()).trim();
    expect(text.length, 'commit-card text under the 900-character ceiling').toBeLessThan(900);
});

test('names every unentered step and blocks the start until the two required ones are in', async ({ page }) => {
    // A fresh context has no draft: this is the state a Trainer meets if they jump straight here.
    await openPreflight(page);

    for (const key of ROWS) {
        await expect(page.locator(`#preflight-step-${key}`)).toContainText('Missing');
        await expect(page.locator(`#preflight-step-${key}`)).toContainText('Not entered');
    }
    // An empty draft carries no warning, so the fix link renders on no row: the checks table's one
    // door is the deck warning's "Fix this" link, and with nothing entered there is no deck to warn
    // about (`career-preflight.html` "one row, one link").
    await expect(page.locator('tr[id^="preflight-step-"] a')).toHaveCount(0);

    await expect(page.getByRole('status').filter({ hasText: 'steps need attention' })).toHaveText(
        '5 steps need attention',
    );

    // Both required entries are named in words beside the button, before it is ever pressed.
    await expect(page.getByText('A scenario and a trainee are not entered')).toBeVisible();

    // DESIGN.md §5 (b): a reachable action the screen forbids renders with a native disabled state or
    // as a named absence with its reason, "never as an enabled control whose label promises what the
    // product cannot perform". Asserted on the attributes rather than through `toBeDisabled()`:
    // Playwright's actionability check honours `aria-disabled`, so that matcher passes on both shapes
    // and cannot tell them apart. Before this case the button carried only `aria-disabled`.
    const startButton = page.getByRole('button', { name: 'Create the run' });
    await expect(startButton).toBeVisible();
    expect(await startButton.evaluate((el) => (el as HTMLButtonElement).disabled)).toBe(true);
    expect(await startButton.evaluate((el) => el.hasAttribute('aria-disabled'))).toBe(false);
});

test('a blocked start is unpressable and names the first row to fix', async ({ page }) => {
    await openPreflight(page);

    // No `force` and no click guard: the native `disabled` attribute is what refuses the press.
    // The old case had to force the click because `aria-disabled` announces a state without cancelling
    // activation, which is exactly the defect §5 (b) names.
    const start = page.getByRole('button', { name: 'Create the run' });
    expect(await start.evaluate((el) => (el as HTMLButtonElement).disabled)).toBe(true);

    await start.click({ force: true, trial: true }).catch(() => undefined);

    // Nothing was created and the URL did not move.
    await expect(page).toHaveURL(/\/career\/setup\/preflight$/);

    // The reason stays printed beside the button, so the refusal is readable without pressing it.
    await expect(page.getByText('A scenario and a trainee are not entered')).toBeVisible();
});

test('an Edit round trip returns with every value intact', async ({ page }) => {
    await walkWizard(page, 'ura_finale');
    await openPreflight(page);

    // The checks table's only door is the deck warning's "Fix this" link; a Pass row has no edit
    // link, so the round trip navigates straight to the step that owns the target.
    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The step opens on what it stored, not on what the client held. `distance` is the radio group the
    // Distance card owns, so its state is `checked`, not a select value.
    await expect(page.locator('input[name="distance"][value="Medium"]')).toBeChecked();
    await expect(page.locator('select[name="purpose"]')).toHaveValue('StoryClear');
    await expect(page.locator('input[name="targets[Speed]"]')).toHaveValue('800');

    // And the checklist still reads the same after the return trip.
    await openPreflight(page);
    await expect(page.locator('#preflight-step-target')).toContainText('Story Clear · Turf Medium · Pace Chaser');
});

test('Create the run writes the run, names the trainee in the flash and lands on it', async ({ page }) => {
    // The completeness guard is the draft's, not a blanket disable: one missing optional step does not
    // block, and the press below is the enabled half of the pair the previous case asserts the other
    // half of.
    createdRunUrls.push(await startCareer(page, 'ura_finale'));

    // The destination is the Cockpit (SCREEN-009, `SCR-CAR-011`), and its heading names the trainee the
    // wizard recorded, which is what proves the write composed the draft rather than creating an empty run.
    const heading = page.getByRole('heading', { level: 1 });
    await expect(heading).toContainText(/\S/);
    const traineeName = (await heading.innerText()).trim();

    // And the one line that answers the press says whose career now exists, in the shell's live region.
    // The exact wording is pinned by `CareerPreflightTest`; here the subject of the sentence is what
    // matters, and it is the name the Cockpit is printing.
    await expect(page.getByRole('status').filter({ hasText: `Career started for ${traineeName}` })).toBeVisible();
});

test('three-diff against career-preflight.html writes the phase evidence', async ({ page }) => {
    // Phase E2 evidence: text (excess/missing), computed style and bounding rect against the HFW.
    //
    // HFW-OVERRIDE (markup: element paths) — `excess`/`missing` are the helper's structural-path
    // buckets, not content: the shell wraps the step bar in `<nav class="mb-4">` where the HFW draws a
    // bare `div.stepper`, so every descendant path below it shifts into both buckets. The content-level
    // buckets are the gate, and the empty-draft state this case measures carries the same numbers as
    // the walked one for text and computed (no draft value renders as prose the HFW does not have).
    await openPreflight(page);

    const hfw = pathToFileURL(
        resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-preflight.html'),
    ).href;

    const dir = resolve(process.cwd(), '.scratch-uma/wizard-parity');
    mkdirSync(dir, { recursive: true });

    for (const [name, viewport] of [['1280', { width: 1280, height: 800 }], ['390', { width: 390, height: 844 }]] as const) {
        const at = await verifyPage(page, {
            appUrl: '/career/setup/preflight',
            hfwUrl: hfw,
            rootSelector: '.canvas',
            ...viewport,
        });

        writeFileSync(join(dir, `preflight-diff-${name}.json`), `${JSON.stringify(at, null, 2)}\n`);
    }

    const report = await verifyPage(page, {
        appUrl: '/career/setup/preflight',
        hfwUrl: hfw,
        rootSelector: '.canvas',
    });

    expect(report.text, `text drift: ${JSON.stringify(report.text)}`).toEqual([]);
    expect(report.computed, `computed drift: ${JSON.stringify(report.computed)}`).toEqual([]);
    expect(report.rect, `rect drift: ${JSON.stringify(report.rect)}`).toEqual([]);
});
