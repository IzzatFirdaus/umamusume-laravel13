import { test, expect } from '@playwright/test';
import { deleteSeededSession, seedCompletedSession } from '../utils/indep-session';

/*
 * Rendered-copy evidence for the Independent Training completion banner
 * (`UMAMUSUME_REFERENCE.md` §1.1.7).
 *
 * `CareerResultTest` asserts the props; this file asserts the banner a Trainer reads — the policy the
 * session ran under, the fans and sashes it paid, and the six limits stated in words so the Trainer
 * knows what an idle run did not do.
 */

const seeded: number[] = [];

test.afterEach(() => {
    for (const runId of seeded.splice(0)) {
        deleteSeededSession(runId);
    }
});

test('renders the completion banner with fans, sashes and the six limits', async ({ page }) => {
    const { runId } = seedCompletedSession({ fans: 1_300_000, sashes: 1 });
    seeded.push(runId);

    await page.goto(`/training-runs/${runId}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const banner = page.getByRole('region', { name: 'Independent Training complete' });
    await expect(banner).toBeVisible({ timeout: 60_000 });

    // The policy it ran under, from the enum's own client noun.
    await expect(banner).toContainText('Balanced policy');

    // What the simulator paid, formatted for reading.
    await expect(banner).toContainText('1,300,000');
    await expect(banner).toContainText("winner's sashes");

    // The six limits, in the words the reference states them.
    const limits = banner.locator('p').filter({ hasText: 'awards no race trophies' });
    await expect(limits).toContainText('no race trophies', { exact: false });
    await expect(limits).toContainText('Winning Live or Gallery', { exact: false });
    await expect(limits).toContainText('Career events or concerts', { exact: false });
    await expect(limits).toContainText('rest', { exact: false });
    await expect(limits).toContainText('growth-rate row', { exact: false });
});
