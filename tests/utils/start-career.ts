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
 * dialog's own "Equip" and "Save deck" buttons. A wizard pass that restructures either step has to
 * move this helper with it.
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

    // The chosen trainee's name, read back from the draft rather than assumed.
    await page.getByText('Stored choice:').first().waitFor();
    const traineeName = (
        await page
            .locator('p', { has: page.getByText('Stored choice:') })
            .locator('strong')
            .first()
            .textContent()
    )?.trim() ?? '';

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
    await page.getByRole('button', { name: 'Save deck' }).click();
    await page.locator('#app > *').first().waitFor();

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
    // The completable-start case: `aria-disabled` is `false` once the scenario and the trainee are in.
    await page.getByRole('button', { name: 'Start career' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    return page.url().replace(/\/cockpit$/, '');
}
