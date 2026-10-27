import { test, expect } from '@playwright/test';
import { deleteSeededSession, seedInProgressSession } from '../utils/indep-session';

/*
 * Rendered-copy evidence for the dashboard timer card (`UMAMUSUME_REFERENCE.md` §1.1.7).
 *
 * `IndependentTrainingDashboardTest` asserts the resolved `indep` prop and the abandon route; this file
 * asserts what only a browser reaches — that an in-progress session renders its countdown, the
 * one-at-a-time rule, and the Give up door, and that the door actually fires the abandon write.
 */

const seeded: number[] = [];

test.afterEach(() => {
    for (const runId of seeded.splice(0)) {
        deleteSeededSession(runId);
    }
});

test('shows the countdown, the one-at-a-time rule and a working Give up', async ({ page }) => {
    const { runId } = seedInProgressSession();
    seeded.push(runId);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const card = page.getByRole('region', { name: 'Pick up where you left off' });
    await expect(card).toContainText('Independent Training in progress', { timeout: 60_000 });

    // The countdown is computed from the stored instant: an mm:ss pair.
    await expect(card.getByText(/\d+:\d{2} remaining/)).toBeVisible();

    // The policy the session was started with, from the enum's own client noun.
    await expect(card).toContainText('Balanced');

    await expect(card).toContainText('You cannot start another training until this one completes.');

    const giveUp = card.getByRole('button', { name: 'Give up' });
    await expect(giveUp).toBeVisible();

    await giveUp.click();

    // The abandon write redirects home, and the card no longer names an in-progress session.
    await page.waitForLoadState('domcontentloaded');
    await expect(page.locator('#app > *').first()).toBeVisible({ timeout: 60_000 });
    await expect(page.getByText('Independent Training in progress')).toHaveCount(0, { timeout: 60_000 });
});
