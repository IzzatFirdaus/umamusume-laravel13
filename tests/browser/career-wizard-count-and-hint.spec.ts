import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { deleteRun } from '../utils/delete-run';

/*
 * F-01 — the scenario continuation hint outlives the condition it names.
 *
 * `ScenarioSelect.vue` rendered `#scenario-continue-hint` ("Choose a scenario to continue.")
 * unconditionally, while only the `aria-describedby` association was conditional. Once a scenario was
 * chosen the button enabled and the description cleared, but the sentence naming an unmet condition
 * stayed on screen — describing a state the page had left.
 *
 * No existing wizard spec covered it: `career-scenario-select.spec.ts` asserts that choosing works,
 * not that the words beside the control stop claiming it has not happened.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 120_000 });

test.describe('F-01 — the scenario continuation hint', () => {
    test('the hint leaves the screen once a scenario is chosen', async ({ page }) => {
        await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const hint = page.locator('#scenario-continue-hint');

        // Precondition: before a choice the hint is the right sentence, and the primary is disabled.
        await expect(hint, 'no hint was rendered before a scenario was chosen').toBeVisible();
        await expect(hint).toHaveText(/choose a scenario to continue/i);

        const primary = page.getByRole('button', { name: /continue to trainee/i }).first();
        await expect(primary, 'the primary was enabled before a scenario was chosen').toBeDisabled();

        await page.locator('#scenario-radio-unity_cup').click();

        // The choice landed: the primary is now usable.
        await expect(primary, 'choosing a scenario did not enable the primary').toBeEnabled();

        /*
         * The defect. `aria-describedby` is cleared by the same fix that should clear the words, so
         * asserting the attribute alone would pass on the broken screen — the sentence is still
         * visible, it is just no longer announced as a description. The assertion is therefore on
         * what a sighted Trainer reads.
         */
        await expect(
            hint,
            'the hint still says a scenario must be chosen although one is chosen',
        ).toBeHidden();
    });
});
