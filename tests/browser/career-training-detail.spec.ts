import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

/*
 * Rendered evidence for SCREEN-010, the Training Decision detail, rebuilt at the HFW's region model
 * after the 2026-10-28 parity audit. The page is now the wireframe's five-discipline pick list, the
 * three other actions, "Why Rest", the four outcome fields and the savebar; the client totals the
 * write boundary requires live in one disclosure that is open by default (round 5's S7 made it open in
 * response to walk 5's E2-01), which is the documented deviation
 * (HFW OVERRIDE (write fields) in the page).
 *
 * The cases below pin that structure and the contract that has not changed: the write still validates
 * against the turn request, the accelerator is printed and works, and Rest writes through the same
 * form. The old cards/Advisor/facility-first assertions were removed with the structure they pinned;
 * this is a re-baseline, not a weakening: every claim the old file made that still applies is present.
 */

const createdRunUrls: string[] = [];
const WRITE = { timeout: 60_000 } as const;

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** The first turn, recorded through this screen's own totals disclosure and savebar. */
async function recordFirstTurn(page: import('@playwright/test').Page, runUrl: string): Promise<void> {
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('radio', { name: 'Speed training' }).check();
    // R7 S0: the disclosure ships `open` (`TrainingDetail.vue:740`, round 5's S7 fix for walk 5's E2-01
    // "required numbers hide inside a collapsed summary"). Clicking the summary therefore CLOSED it and
    // hid the very fields this helper fills. Opening conditionally keeps the helper correct whether the
    // attribute is there or not, so a future flip does not silently break every case again.
    const totals = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });
    if (!(await totals.evaluate((el) => (el as HTMLDetailsElement).open))) {
        await totals.locator('summary').click();
    }

    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('input[name="fans"]').fill('120');
    await page.locator('input[name="sp"]').fill('100');
    // Facility levels: the discipline list reads its published gains and Energy from the level the
    // run records, so a turn that records none states the absence instead of a Lv-1 stand-in.
    await page.locator('input[name="facility_speed"]').fill('1');
    await page.locator('input[name="facility_stamina"]').fill('1');
    await page.locator('input[name="facility_power"]').fill('1');
    await page.locator('input[name="facility_guts"]').fill('1');
    await page.locator('input[name="facility_wit"]').fill('1');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 1 logged.')).toBeVisible(WRITE);

    // UX-16: the topbar pill and the Training screen's button count different things, and both numbers
    // are right — the pill states the position the run stands at (turn 1, now logged), the button names
    // the turn about to be recorded (turn 2). The pill says "logged" so the pair reads as a sequence
    // rather than as an off-by-one. The audit filed the arithmetic as a defect; it was the frame.
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/Turn 1 logged/)).toBeVisible();
    await expect(page.getByRole('button', { name: 'Record turn 2' })).toBeVisible();
}

async function openTrainingDetail(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);
    await recordFirstTurn(page, runUrl);
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model, one primary, and one savebar', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.getByRole('heading', { name: 'Disciplines', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Other actions', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: /^Why /, level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Recorded outcome', level: 2 })).toBeVisible();

    // Five disciplines plus Rest, Recreation and Race = eight radios, one savebar, one filled action.
    await expect(page.locator('input[name="pick"]')).toHaveCount(8);
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('.btn-primary')).toHaveCount(1);
});

test('prints the five disciplines with their published gains and a signed Energy figure', async ({ page }) => {
    await openTrainingDetail(page);

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(page.getByRole('radio', { name: `${stat} training` })).toBeVisible();
    }

    // Four Energy disciplines spend Energy and Wit returns it; both are signed and worded.
    await expect(page.getByText(/^−\d+ Energy$/)).toHaveCount(4);
    await expect(page.getByText(/^\+\d+ Energy$/)).toHaveCount(1);
    await expect(page.getByText('no Energy', { exact: true })).toBeVisible();
});

test('marks the advisor\'s recommendation once', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.getByText('Recommended')).toHaveCount(1);
});

test('selects a discipline with the printed 1-5 accelerator', async ({ page }) => {
    await openTrainingDetail(page);

    await page.keyboard.press('3');

    await expect(page.getByRole('radio', { name: 'Power training' })).toBeChecked();
    await expect(page.locator('.savebar')).toContainText('Power selected');
});

test('Rest is one of the other actions and writes through the same form', async ({ page }) => {
    await openTrainingDetail(page);

    await page.getByRole('radio', { name: 'Rest' }).check();
    await expect(page.locator('.savebar')).toContainText('Rest selected');

    // S0: only click when the disclosure is closed; it ships open, so an unconditional click hides the
    // fields this case is about to fill.
    const totals = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });
    if (!(await totals.evaluate((el) => (el as HTMLDetailsElement).open))) {
        await totals.locator('summary').click();
    }
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('90');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
});

test('the client totals stay in one disclosure, open by default (S0)', async ({ page }) => {
    // This case asserted `closed by default` until R7's S0 housekeeping. Walk 5's E2-01 recorded the
    // closed state as the defect — "the required numbers hide inside a collapsed summary" — and round
    // 5's S7 fixed it by making the disclosure open (`TrainingDetail.vue:740`). Round 6's C1 dated that
    // fix to `dcc562b6` (2026-11-04). The assertion was never updated, so it pinned the pre-fix state.
    await openTrainingDetail(page);

    const details = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });

    await expect(details).toHaveAttribute('open', '');
    await expect(page.locator('input[name="speed"]')).toBeVisible();
});

test('names the turn field as the turn to play and states what the index means (R7-20)', async ({ page }) => {
    await openTrainingDetail(page);

    // R7-20's owner ruling is (a). The field's VALUE is already correct — the server sends
    // `nextTurnNumber()` (`TrainingDecisionController.php:574`), which is 1 on a fresh run and 68 on a
    // mid-career one. What walk 6 fell over was the affordance: `Turn *` reads as "the run's position"
    // while the value is the turn about to be recorded, so a Trainer importing a later position never
    // learns the field is where that position goes.
    const totals = page.locator('details').filter({ hasText: 'Client totals this turn (required to record)' });
    const label = totals.locator('label').filter({ hasText: /Turn to play/ });

    await expect(label).toHaveCount(1);
    await expect(label).toContainText("the run's next turn");

    // The helper states what the index is, and names the mapping the client uses, so the number a
    // Trainer types is the one their own screen showed them.
    await expect(totals.getByText(/as the client shows this turn/i)).toBeVisible();
});

test('keeps the tab order and the 44px target floor, and passes axe', async ({ page }) => {
    await openTrainingDetail(page);

    // The hit target is the label that wraps a radio, not the 13px radio glyph inside it, so this
    // measures links, buttons, summaries and selects; a radio's wrapper is asserted by its own label
    // height through the discipline rows' min-h-11 padding.
    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});

test('holds its layout at 320px', async ({ page }) => {
    await openTrainingDetail(page);
    await page.setViewportSize({ width: 320, height: 900 });

    const overflows = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);

    expect(overflows).toBe(false);

});

test('renders the undo disabled, matching what it says about itself (R7-21)', async ({ page }) => {
    // The control's own title reads "The build has no undo; this screen proposes one." and it was
    // rendered enabled, so it advertised an action that does not exist. `RaceDecision.vue:461` had
    // it right the whole time: disabled, same sentence. Round 7 R7-21.
    await openTrainingDetail(page);

    const undo = page.getByRole('button', { name: /Undo turn \d+/ });

    await expect(undo).toBeVisible();
    await expect(undo).toBeDisabled();

    // The label still names a real turn: an undo would remove the last logged one, so this is not a
    // placeholder number and must not become one.
    await expect(undo).toHaveAccessibleName(/Undo turn \d+/);
});

test('renders the seven preview readings labelled without a click (R7-57)', async ({ page }) => {
    // The seven inputs carry real `<label>` wrappers in source, so a source read says they are labelled.
    // They sat inside a `<details>` that was closed by default, and a closed `<details>` renders none of
    // its contents: the walk read seven bare number boxes. Round 5 answered the identical shape for
    // `Client totals this turn` by opening the group (`TrainingDetail.vue:740`); this is the same fix for
    // the same reason, and it is what R7-57's "unlabelled" finding measured.
    await openTrainingDetail(page);

    // Scoped to the group by id prefix: `Speed` alone also matches the turn's own stat input, so an
    // unscoped label lookup would pass on the wrong field.
    await expect(page.locator('#turn-failure_rate')).toBeVisible();
    await expect(page.locator('#turn-preview_gains-speed')).toBeVisible();
    await expect(page.locator('#turn-preview_gains-sp')).toBeVisible();

    // All seven at once, so a future half-revert fails here rather than passing on one field.
    await expect(page.locator('[id^="turn-preview_gains-"]')).toHaveCount(6);
    await expect(page.getByLabel('Failure rate')).toBeVisible();
});
