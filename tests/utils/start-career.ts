import { expect, type Page } from '@playwright/test';

/*
 * Starts a career through the wizard's own steps and returns the run's record URL.
 *
 * Extracted from `career-cockpit.spec.ts`'s private `newCareer` so the Dashboard and the wizard
 * passes can both reach the active-career state without each owning a copy of the walk. Every fact
 * under test is entered through the app rather than seeded, which is the same fixture strategy the
 * cockpit spec states.
 *
 * The helper deliberately does NOT own cleanup: it returns the bare record URL and the calling spec
 * tracks it, because each spec owns its `afterEach` teardown (`tests/utils/delete-run.ts` cascades
 * the run's rows). Keeping the array in the spec keeps the fixture lifecycle next to the assertions
 * that depend on it.
 *
 * Each write is given 60s rather than the config's 15s. `php artisan serve` is a single-process
 * `php -S`, so a step's PUT queues behind whatever else is on the box; `playwright.config.ts`
 * already raised the test budget to 180s for exactly that reason.
 *
 * Dated note 2026-10-19: the walk was re-pointed onto the DOM the wizard actually ships. Three
 * rebuilds had left it naming controls that no longer exist, and every spec that starts a career
 * through it was red: step 1's option cards became a radio group whose card IS the label (commit
 * `3e8e924`), so the control is `#scenario-radio-<key>` and its state is `aria-checked`; step 2's
 * per-card action is `#trainee-<id>` labelled `Select`; and step 5's always-visible picker column
 * became the `CardPicker` dialog behind each slot's "Choose a card" opener, with the ownership flag
 * as a second, separate write. The waits below are all on SERVER-driven state (`aria-checked` and
 * `aria-pressed` are bound to the props the PUT returns, and the slot's empty state is bound to the
 * draft), so each one is the round trip's confirmation rather than a client-side repaint.
 *
 * Coupling to watch: step 3 is driven by its current field names (`select[name="purpose"]`,
 * `input[name="targets[Speed]"]`, the "Save target" button) and step 5 by its slot ids and the
 * dialog's own "Equip" and "Owned" controls. A wizard pass that restructures either step has to
 * move this helper with it. Dated note 2026-10-28 (Phase E): the page-level "Save deck" submit is
 * gone — every slot write saves on its own — so the walk's last deck action is the ownership press.
 */
const WRITE = { timeout: 60_000 } as const;

/**
 * Walk the six steps as far as the draft, without creating anything.
 *
 * Returns the trainee name the draft holds, read back from the step rather than assumed: the
 * journey spec asserts that exact name on the screens it later lands on, and a walk that swallowed it
 * would force every caller to re-implement the same read.
 *
 * `scenario` names the scenario card to pick; `unity_cup` is the one the seeded scratch database
 * carries a full rule set for. The ancestry step is not filled — the seeded database holds no
 * Veterans — but `options.onLegacy` still hands the caller the page when it needs to assert the step
 * itself, which is how the journey keeps its own assertions without owning a second walk.
 *
 * Dated note 2026-10-19 (slice 4.1): `onLegacy` and the returned name are the two additive interface
 * changes the four stale private walks needed. The walks agreed on everything else, so they now call
 * this helper and the copies are gone.
 */
export async function walkWizard(
    page: Page,
    scenario = 'unity_cup',
    options: { onLegacy?: (page: Page) => Promise<void> } = {},
): Promise<string> {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator(`#scenario-radio-${scenario}`).click();
    await expect(page.locator(`#scenario-radio-${scenario}`)).toHaveAttribute('aria-checked', 'true', WRITE);

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.locator('button[id^="trainee-"]').first();
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true', WRITE);

    // The chosen trainee's name, read back from the echo card the wizard actually renders.
    //
    // Dated note 2026-10-27 (wizard parity audit, Phase A): the carried-choices strip this used to read
    // (`[data-field="choices-so-far"]`) was cut from `SetupLayout`, because no career setup wireframe
    // draws one. The echo card is the page's own statement of the same fact and predates the strip, so
    // the read moved there rather than the strip staying for the helper's benefit.
    const echo = page.locator('[data-field="selected-trainee"]');
    await echo.waitFor();
    const traineeName = (await echo.locator('p').first().innerText()).trim();

    expect(traineeName, 'the selection echo names no trainee after the pick').not.toBe('');

    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.selectOption('select[name="purpose"]', 'StoryClear');
    // `distance` is a radio group on the Distance card, not a select: the target step's own 2026-10-2x
    // rebuild moved it there (`BuildTarget.vue`, the wireframe's `career-target.html:141-158`).
    await page.locator('input[name="distance"][value="Medium"]').check();
    await page.selectOption('select[name="surface"]', 'Turf');
    await page.selectOption('select[name="style"]', 'Pace Chaser');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="targets[${stat}]"]`).fill('800');
    }

    // The step's write is its head-band forward door (`BuildTarget.vue`, Phase A.2): a submit bound to
    // the form by id, labelled with the wireframe's own words. The old page-level "Save target" button
    // is gone, so the walk presses the door it actually has.
    await page.getByRole('button', { name: 'Continue to legacy' }).click();
    await page.locator('#app > *').first().waitFor();

    if (options.onLegacy) {
        await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await options.onLegacy(page);
    }

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // One card in position 1, and the flag beside it: `StartCareerRequest` refuses a card-holding
    // slot whose ownership was never chosen, so a walk that only equipped would stop at Preflight.
    await page.locator('#deck-slot-1 button[aria-label^="Choose a card"]').click();
    await page.getByRole('dialog').getByRole('button', { name: 'Equip' }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Empty.', WRITE);
    await page.locator('#deck-slot-1').getByRole('button', { name: 'Owned' }).click();
    await expect(page.locator('#deck-slot-1').getByRole('button', { name: 'Owned' })).toHaveAttribute(
        'aria-pressed',
        'true',
        WRITE,
    );
    // The ownership press is the last write: the slot's Owned button writes on click (there is no
    // page-level `Save deck` since Phase E, and the forward door writes before it navigates), so the
    // `aria-pressed` assertion above is already the round trip's confirmation.

    return traineeName;
}

/**
 * Walk the six steps and press `Start career`.
 *
 * Returns the run's RECORD URL rather than the Cockpit the write lands on, because cleanup addresses
 * the run by the id in that path. The browser is LEFT on the Cockpit, so a caller that needs the
 * Cockpit URL uses `page.url()`; a caller that needs the record URL uses the return value.
 */
export async function startCareer(
    page: Page,
    scenario = 'unity_cup',
    options: { onLegacy?: (page: Page) => Promise<void> } = {},
): Promise<string> {
    await walkWizard(page, scenario, options);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The completable-start case: the button is enabled once a scenario and a trainee are in. Its label
    // is the wireframe's own (`career-preflight.html:64`, "Create the run"); the Phase E2 cut removed
    // the Independent-Training branch that used to swap the text.
    await page.getByRole('button', { name: 'Create the run' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    return page.url().replace(/\/cockpit$/, '');
}
