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
 * Coupling to watch: the target step is driven by its current field names (`select[name="purpose"]`,
 * `input[name="targets[Speed]"]`, the "Save target" button). A wizard pass that restructures that
 * step has to move this helper with it.
 */
const WRITE = { timeout: 60_000 } as const;

/**
 * Walk the six steps and press `Start Career`.
 *
 * `scenario` names the scenario card to pick; `unity_cup` is the one the seeded scratch database
 * carries a full rule set for. The ancestry step is left alone, which is the state a fresh install
 * meets (the seeded database holds no Veterans).
 */
export async function startCareer(page: Page, scenario = 'unity_cup'): Promise<string> {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator(`#scenario-${scenario}`).click();
    await expect(page.locator(`#scenario-${scenario}`)).toHaveAttribute('aria-pressed', 'true', WRITE);

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
    // `Start Career` lands on the Cockpit, not the run record screen (SCR-CAR-010 redirects to
    // `runs.cockpit`), so this waits for the Cockpit and returns the *record* URL: cleanup
    // addresses the run by the id in that path, which is the plainest form of it.
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    return page.url().replace(/\/cockpit$/, '');
}
