import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { deleteSeededSession, seedCompletedSession, seedInProgressSession } from '../utils/indep-session';

/*
 * Screenshot capture for the four Independent Training surfaces (`UMAMUSUME_REFERENCE.md` §1.1.7 and
 * the §1.1.6 mood columns), at the two viewports the DoD names.
 *
 * This is a capture run, not a behavioural spec: each case asserts the surface rendered (so a shot is
 * never taken of an error page) and then writes the PNG. Kept in the suite rather than a loose script
 * because it reuses the seeding helpers and the harness's own scratch database.
 *
 * Run with: npx playwright test tests/browser/_screens-indep-training.spec.ts --reporter=list
 * The leading underscore keeps it out of the default pattern's alphabet for readers looking for specs.
 */

const DIR = '.scratch-uma/session-2026-10-29/screenshots';

const VIEWPORTS = [
    { suffix: '1280', width: 1280, height: 800 },
    { suffix: '390', width: 390, height: 844 },
] as const;

const seeded: number[] = [];

test.afterEach(() => {
    for (const runId of seeded.splice(0)) {
        deleteSeededSession(runId);
    }
});

test.beforeAll(() => {
    mkdirSync(DIR, { recursive: true });
});

for (const viewport of VIEWPORTS) {
    test(`preflight entry tab at ${viewport.width}`, async ({ page }) => {
        await page.setViewportSize({ width: viewport.width, height: viewport.height });

        await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.locator('#scenario-radio-unity_cup').click();
        await expect(page.locator('#scenario-radio-unity_cup')).toHaveAttribute('aria-checked', 'true', { timeout: 60_000 });

        await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.locator('button[id^="trainee-"]').first().click();

        await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.getByRole('group', { name: 'Career mode' }).getByRole('button', { name: 'Independent Training' }).click();
        await expect(page.getByRole('group', { name: 'Training policy' })).toBeVisible({ timeout: 60_000 });

        await page.screenshot({ path: `${DIR}/career-preflight-indep-${viewport.suffix}.png`, fullPage: true });
    });

    test(`dashboard timer card at ${viewport.width}`, async ({ page }) => {
        const { runId } = seedInProgressSession();
        seeded.push(runId);

        await page.setViewportSize({ width: viewport.width, height: viewport.height });
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const card = page.getByRole('region', { name: 'Pick up where you left off' });
        await expect(card).toContainText('Independent Training in progress', { timeout: 60_000 });

        await page.screenshot({ path: `${DIR}/dashboard-indep-timer-${viewport.suffix}.png`, fullPage: true });
    });

    test(`result banner at ${viewport.width}`, async ({ page }) => {
        const { runId } = seedCompletedSession();
        seeded.push(runId);

        await page.setViewportSize({ width: viewport.width, height: viewport.height });
        await page.goto(`/training-runs/${runId}/result`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const banner = page.getByRole('region', { name: 'Independent Training complete' });
        await expect(banner).toBeVisible({ timeout: 60_000 });

        await page.screenshot({ path: `${DIR}/career-result-indep-${viewport.suffix}.png`, fullPage: true });
    });

    test(`cockpit mood columns at ${viewport.width}`, async ({ page }) => {
        // §1.1.6: the trainer reads the tier's two columns side by side. A run with one logged turn
        // carrying a Mood tier is enough; the card prints training and running beside it.
        const { runId } = seedInProgressSession({ mood: 'GREAT' });
        seeded.push(runId);

        await page.setViewportSize({ width: viewport.width, height: viewport.height });
        await page.goto(`/training-runs/${runId}/cockpit`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // Both columns are the surface under test; assert them before shooting so a shot is never
        // taken of a panel that failed to render the pair.
        await expect(page.getByText(/training \+\d+%, running \+\d+%/).first()).toBeVisible({ timeout: 60_000 });

        await page.screenshot({ path: `${DIR}/cockpit-mood-columns-${viewport.suffix}.png`, fullPage: true });
    });
}
