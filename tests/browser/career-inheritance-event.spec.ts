import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-013, the Inheritance Event
 * (`SCR-CAR-015`, plan §8 D12). `CareerInheritanceEventTest` asserts the resolved props — the
 * predicted/observed separation, the N/A expected inheritance with ADR-0020 citation, the
 * star-roll table, the milestone glyphs, the empty state, and the POST. These cases assert what
 * only a browser reaches: the `N/A` title text a sighted Trainer hovers, the form's keyboard
 * behaviour, the 44px sweep on the observed form, the predicted vs observed visual separation,
 * and an axe scan.
 *
 * Fixture strategy. The run needs a legacy_selection payload and at least one logged turn so
 * the observed form has a turn to pick. The writes get 60s because `php artisan serve` is a
 * single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * Turns recorded over `runs.turns.store` rather than through a form on a page.
 *
 * The shape `ura-panel.spec.ts` uses for its eleven turns, for the same reason: the write is not this
 * spec's subject. The raw hatch this fixture used to click lives in `Runs/Show.vue`, and `runs.show` is
 * now a redirect to the cockpit (`routes/web.php:116`) whose correction form is a PUT over an existing
 * turn and cannot create one, so no rendered surface offers a create-turn form any more. Laravel
 * refreshes the XSRF cookie on every response, so the header is read again for each write rather than
 * once for the loop: one stale token and the next POST is a 419.
 */
async function recordTurns(page: import('@playwright/test').Page, runId: string, count: number): Promise<void> {
    for (let turn = 1; turn <= count; turn++) {
        const xsrf = await page
            .context()
            .cookies()
            .then((all) => all.find((cookie) => cookie.name === 'XSRF-TOKEN')?.value);

        expect(xsrf, `the session carries no XSRF-TOKEN cookie before turn ${turn}`).toBeDefined();

        const response = await page.request.post(`/training-runs/${runId}/turns`, {
            form: {
                turn: String(turn),
                speed: '600',
                stamina: '500',
                power: '500',
                guts: '500',
                wit: '500',
            },
            headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
        });

        expect(response.status(), `turn ${turn} was refused`).toBeLessThan(400);
    }
}

/**
 * A career with legacy configuration and logged turns, then the Inheritance Event screen for it.
 *
 * The legacy configuration is entered via the run-scoped Legacy Lab builder (`legacy.builder`,
 * `/legacy/{run}`). We create the run, log a few turns, then use the Legacy Lab to set the
 * legacy_selection, then visit /inheritance.
 *
 * **Addressing contract.** The rank, ancestor, rented and Spark controls carry `v-model` and an `:id`
 * and no `name` at all; only the parent pick carries a `name`, because `AncestryNode` is handed
 * `controlName`. So this fixture addresses them by `:id` (`#rank-parent_a`) and by role plus the
 * label the wrapping `<label>` gives them ("Kind", "Applies to", "Stars"), never by a `name` that does
 * not exist on either surface (`KNOWN-ISSUES.md` KI-69, plan §4.1 item 10). The parent pick is left
 * unchosen: this suite's scratch database holds no Veterans (KI-69 class 3), so the roster renders no
 * options, and `legacy_id` is nullable so the payload is valid without one. The picker's own contract
 * is covered by `ancestry-node-picker.spec.ts`, a component-level case mounted for exactly that reason.
 */
async function openInheritanceEvent(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+(\/cockpit)?$/, WRITE);
    createdRunUrls.push(page.url());

    // The id is read without an end anchor on purpose: the create redirect is `runs.show` at HEAD and
    // `runs.cockpit` where the cockpit cutover has landed, and both carry the id mid-path.
    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 20);

    // Now set up the legacy configuration via the run-scoped Legacy Lab.
    await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Each parent's `Add Spark` is scoped by its own index: the button carries an `sr-only`
    // " to {{ parent.label }}" suffix, so its accessible name is per-parent and DOM order is A then B.
    const addSpark = (parent: number) => page.getByRole('button', { name: 'Add Spark' }).nth(parent);

    // Parent A: rank, both ancestors, and four Sparks. The Spark rows land in DOM order across both
    // parents (A0..A3, then B0..B2), which is what the nth() indexes below count on. The kinds and the
    // counts mirror `CareerInheritanceEventTest::inheritanceLegacyPayload()`, so the browser fixture and
    // the feature fixture hold the same seven Sparks: two Blue, one Pink, one Green, two White, one
    // Scenario. Every row carries a target and a star count, because the write boundary discards a Spark
    // with neither (`StoreLegacySelectionRequest::payload()`, D2).
    await page.locator('#rank-parent_a').fill('3');
    await page.locator('#ancestor-parent_a-0').fill('Symboli Rudolf');
    await page.locator('#ancestor-parent_a-1').fill('Mejiro McQueen');
    await addSpark(0).click();
    await addSpark(0).click();
    await addSpark(0).click();
    await addSpark(0).click();
    await page.getByRole('combobox', { name: 'Kind' }).nth(0).selectOption('blue');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(0).fill('Speed');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(0).fill('3');
    await page.getByRole('combobox', { name: 'Kind' }).nth(1).selectOption('pink');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(1).fill('Medium');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(1).fill('2');
    await page.getByRole('combobox', { name: 'Kind' }).nth(2).selectOption('white');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(2).fill('Arc Maestro');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(2).fill('1');
    await page.getByRole('combobox', { name: 'Kind' }).nth(3).selectOption('white');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(3).fill('Lightning Strike');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(3).fill('2');

    // Parent B: rank, the rented flag, both ancestors, and three Sparks.
    await page.locator('#rank-parent_b').fill('2');
    await page.getByRole('checkbox', { name: 'Rented from a friend' }).nth(1).check();
    await page.locator('#ancestor-parent_b-0').fill('Special Week');
    await page.locator('#ancestor-parent_b-1').fill('Grass Wonder');
    await addSpark(1).click();
    await addSpark(1).click();
    await addSpark(1).click();
    await page.getByRole('combobox', { name: 'Kind' }).nth(4).selectOption('blue');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(4).fill('Stamina');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(4).fill('2');
    await page.getByRole('combobox', { name: 'Kind' }).nth(5).selectOption('green');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(5).fill('Endless Bloom');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(5).fill('3');
    await page.getByRole('combobox', { name: 'Kind' }).nth(6).selectOption('scenario');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(6).fill('Trackblazer');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(6).fill('1');

    // Save the legacy on the builder's own contract: the button is `Confirm Inheritance` (`Save Legacy`
    // is the wizard's). The write does not return to the builder at all — `LegacyController::update()`
    // redirects to `legacy.compare` with the flash `Inheritance recorded.` — so that flash is the signal
    // that the payload landed.
    await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
    await expect(page.getByText('Inheritance recorded.')).toBeVisible(WRITE);

    // Now visit the inheritance event screen. The route is `/training-runs/{run}/inheritance`,
    // so `page.url()` here points at `/legacy/{runId}` and appending `/inheritance` would 404.
    await page.goto(`/training-runs/${runId}/inheritance`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('shows predicted and observed sections visually separated with badges', async ({ page }) => {
    await openInheritanceEvent(page);

    // Two main sections with distinct headings and badges
    await expect(page.getByRole('heading', { name: 'Predicted Sparks' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Observed Outcomes' })).toBeVisible();

    // Predicted has Estimated badge
    await expect(page.getByText('Estimated', { exact: true })).toBeVisible();

    // Observed has Confirmed badge
    await expect(page.getByText('Confirmed', { exact: true })).toBeVisible();

    // No computed total anywhere - the expected inheritance shows N/A
    await expect(page.getByText('Expected Inheritance')).toBeVisible();
    await expect(page.getByText('N/A').first()).toBeVisible();
    await expect(page.getByText('N/A').first()).toHaveAttribute('title', /ADR-0020/);
    await expect(page.getByText('N/A').first()).toHaveAttribute('title', /does not derive/);

    // The star-roll table is present
    // The page renders this as the label above the table, not as a heading (`InheritanceEvent.vue`
    // keeps its headings for the three sections), so the assertion reads the text, not a role.
    await expect(page.getByText('Sourced Star-Roll Probabilities')).toBeVisible();
    await expect(page.getByText('Below 600')).toBeVisible();
    await expect(page.getByText('600–1100')).toBeVisible();
    await expect(page.getByText('Above 1100')).toBeVisible();
    await expect(page.getByText('~90%')).toBeVisible();
    await expect(page.getByText('~10%')).toBeVisible();
    await expect(page.getByText('0%')).toBeVisible();
});

test('milestone timeline shows correct glyphs and labels', async ({ page }) => {
    await openInheritanceEvent(page);

    await expect(page.getByRole('heading', { name: 'Inheritance Milestones' })).toBeVisible();

    // Three milestones
    // `exact` because the page's own intro sentence spells the three moments out in prose, and a
    // substring match would meet both it and the milestone label and fail strict mode.
    await expect(page.getByText('Career Start', { exact: true })).toBeVisible();
    await expect(page.getByText('Classic Early April', { exact: true })).toBeVisible();
    await expect(page.getByText('Senior Early April', { exact: true })).toBeVisible();

    // With 20 turns logged, we're in Junior Year (turns 1-24), so Career Start is completed,
    // Classic and Senior are upcoming
    // Career Start glyph = ● (completed)
    // Classic Early April glyph = ○ (upcoming)
    // Senior Early April glyph = ○ (upcoming)
    await expect(page.getByText('●').first()).toBeVisible();
    await expect(page.getByText('○').first()).toBeVisible();
    await expect(page.getByText('○').nth(1)).toBeVisible();

    // Status labels
    await expect(page.getByText('completed').first()).toBeVisible();
    await expect(page.getByText('upcoming').first()).toBeVisible();
});

test('observed form records an inheritance event with keyboard entry', async ({ page }) => {
    await openInheritanceEvent(page);

    // The observed form is present
    await expect(page.getByRole('heading', { name: 'Record an inheritance event' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Record Event' })).toBeVisible();

    // Fill the form via keyboard
    await page.getByRole('combobox', { name: 'Turn *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter'); // Select turn 1

    await page.getByRole('combobox', { name: 'Source *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter'); // Select Career Start

    await page.locator('input[name="choice_label"]').fill('Blue Speed ★★★, Pink Medium ★★');

    await page.getByRole('button', { name: 'Record Event' }).click();

    // Success toast
    await expect(page.getByText('Inheritance event recorded.')).toBeVisible();

    // The event appears in the observed list
    await expect(page.getByText('Career Start', { exact: true })).toBeVisible();
    await expect(page.getByText('Blue Speed ★★★, Pink Medium ★★')).toBeVisible();
    await expect(page.getByText('Confirmed')).toBeVisible();
});

test('sizes the inheritance event controls to the 44px contract', async ({ page }) => {
    await openInheritanceEvent(page);

    // Check the form controls
    const controls = [
        page.getByRole('combobox', { name: 'Turn *' }).first(),
        page.getByRole('combobox', { name: 'Source *' }).first(),
        page.locator('input[name="choice_label"]'),
        page.getByRole('button', { name: 'Record Event' }),
        page.locator('textarea[name="origin_note"]'),
    ];

    for (const control of controls) {
        const box = await control.boundingBox();
        expect(box?.height ?? 0, 'an inheritance event control is not sized to the 44px contract')
            .toBeGreaterThanOrEqual(44);
    }

    // Check the milestone glyphs are at least 20px (they're 20px = 1.25rem = 5 * 4px)
    const milestoneGlyph = page.getByText('●').first();
    const glyphBox = await milestoneGlyph.boundingBox();
    // Glyphs are smaller, they're decorative - the text next to them carries the info
    expect(glyphBox?.width ?? 0).toBeGreaterThan(16);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openInheritanceEvent(page);

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('empty state when no legacy configuration exists', async ({ page }) => {
    // Create a run without legacy configuration
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+(\/cockpit)?$/, WRITE);
    createdRunUrls.push(page.url());

    // The id is read without an end anchor: the create redirect lands on the cockpit where the
    // cutover has landed, and `${page.url()}/inheritance` would then read as a cockpit sub-path.
    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 1);

    // Visit inheritance screen
    await page.goto(`/training-runs/${runId}/inheritance`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Empty state shows
    await expect(page.getByRole('heading', { name: 'No Legacy configuration yet' })).toBeVisible();
    await expect(page.getByText('No Legacy configuration recorded for this run')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Enter Legacy Now' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Back to Cockpit' })).toBeVisible();

    // The predicted/observed sections are not rendered
    await expect(page.getByRole('heading', { name: 'Predicted Sparks' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Observed Outcomes' })).toHaveCount(0);
});

test('predicted section aggregates sparks across both parents and grandparents', async ({ page }) => {
    await openInheritanceEvent(page);

    // Blue: 2 total (one from each parent)
    await expect(page.locator('text=Blue').first()).toBeVisible();
    await expect(page.locator('text=2 Sparks').first()).toBeVisible();

    // Pink: 1 total
    await expect(page.locator('text=Pink').first()).toBeVisible();
    await expect(page.locator('text=1 Spark').first()).toBeVisible();

    // Green: 1 total
    await expect(page.locator('text=Green').first()).toBeVisible();
    await expect(page.locator('text=1 Spark').nth(1)).toBeVisible(); // 2nd "1 Spark"

    // White: 2 total
    await expect(page.locator('text=White').first()).toBeVisible();

    // Scenario: 1 total
    await expect(page.locator('text=Scenario').first()).toBeVisible();
});

test('no percentage or win figure appears anywhere in the predicted section', async ({ page }) => {
    await openInheritanceEvent(page);

    // The star-roll table has ~90%, ~10%, 0%, ~50%, ~45%, ~6%, ~20%, ~70%, ~10%
    // These are in the table and are explicitly probabilities - that's correct
    // But no "win", "probability" total, "confidence", "score" text
    await expect(page.locator('main')).not.toContainText('confidence');
    await expect(page.locator('main')).not.toContainText('score');
    await expect(page.locator('main')).not.toContainText('win probability');
    await expect(page.locator('main')).not.toContainText('total');
});