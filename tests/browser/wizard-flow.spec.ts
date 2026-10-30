import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * The wizard as a flow, not as pages. Every existing wizard spec asserts one step's rendered shape; none
 * walked the forward doors, which is how a save-and-stay primary on the Build target step shipped with a
 * green suite (2026-10-29 walkthrough). This case clicks the primary at each step and asserts the URL
 * moves on, ending at the cockpit a real run is created in.
 *
 * The trainee profile is on this linear path now: the trainee step's forward door opens it
 * (`career-trainee.html:64`) and its own primary continues to the build target
 * (`career-profile.html:67`), which is also where that step's back door returns.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 240_000 });

async function clickPrimary(page: import('@playwright/test').Page, name: string): Promise<void> {
    const button = page.getByRole('button', { name }).first();
    const link = page.getByRole('link', { name }).first();

    if (await button.count()) {
        await button.click();
    } else {
        await link.click();
    }

    await page.waitForLoadState('domcontentloaded');
    await page.locator('#app > *').first().waitFor();
}

test('a user can complete the setup wizard from scenario to a created run', async ({ page }) => {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Step 1 — scenario.
    await page.locator('input[type="radio"][name="scenario"]').first().check({ force: true });
    await expect(page.getByText('Scenario set.')).toBeVisible();
    await clickPrimary(page, 'Continue to trainee');
    await expect(page).toHaveURL(/\/career\/setup\/trainee$/);

    // Step 2 — trainee. The card click stores the pick; the forward door opens the profile.
    await page.locator('[data-trainee-card]').first().click();
    await expect(page.getByText(/Trainee set:/)).toBeVisible();
    await clickPrimary(page, 'Continue to build target');
    await expect(page).toHaveURL(/\/career\/setup\/trainee\/\d+$/);

    // Step 2's read screen. Its chips read the same draft: step 1 saved, step 2 current and saved, so
    // the current chip is done rather than "started unfinished".
    await expect(page.getByRole('listitem', { name: 'Scenario, saved' })).toBeVisible();
    const currentChip = page.locator('[data-setup-steps] .step[aria-current="step"]');
    await expect(currentChip).toHaveClass(/is-done/);
    await expect(currentChip).toHaveAttribute('aria-label', 'Trainee, current step');

    await clickPrimary(page, 'Continue to build target');
    await expect(page).toHaveURL(/\/career\/setup\/target$/);

    // The target's back door returns to the profile the Trainer just read (`career-target.html:63`).
    await expect(page.getByRole('link', { name: 'Back', exact: true })).toHaveAttribute('href', /\/career\/setup\/trainee\/\d+$/);

    // Step 3 — build target. The step's validation is all-or-nothing: any entered value makes purpose,
    // distance, surface, style and all five stats required together, so a real completion fills them all.
    await page.getByLabel('Purpose').selectOption('StoryClear');
    await page.getByLabel('Surface').selectOption('Turf');
    await page.getByLabel('Style').selectOption('Pace Chaser');
    await page.locator('input[type="radio"][name="distance"]').first().check({ force: true });
    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.getByLabel(stat, { exact: true }).fill('600');
    }

    await clickPrimary(page, 'Continue to legacy');

    // The defect this spec exists for: the primary must advance, not save and stay.
    await expect(page).toHaveURL(/\/career\/setup\/legacy$/);
    await expect(page.getByText('Build target saved.')).toBeVisible();

    // Step 4 — legacy.
    await clickPrimary(page, 'Continue to deck');
    await expect(page).toHaveURL(/\/career\/setup\/deck$/);

    // Step 5 — deck.
    await clickPrimary(page, 'Continue to preflight');
    await expect(page).toHaveURL(/\/career\/setup\/preflight$/);

    // Step 6 — preflight creates the run and lands on its cockpit.
    await clickPrimary(page, 'Create the run');
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { timeout: 60_000 });
    createdRunUrls.push(page.url());

    await expect(page).toHaveURL(/\/training-runs\/\d+\/cockpit$/);
});
